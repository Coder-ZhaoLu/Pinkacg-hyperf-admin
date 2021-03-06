<?php


namespace App\Services;

use App\Exception\RequestException;
use App\Filters\PostFilter;
use App\Model\Category;
use App\Model\Comment;
use App\Model\Order;
use App\Model\Post;
use App\Model\Tag;
use App\Model\User;
use App\Request\admin\PostRequest;
use App\Resource\admin\PostResource;
use Hyperf\Di\Annotation\Inject;
use Phper666\JWTAuth\JWT;
use Psr\Http\Message\ResponseInterface;
use function PHPUnit\Framework\isNull;

class PostService extends Service
{
    /**
     * @Inject
     * @var PostFilter
     */
    protected $postFilter;

    /**
     * @Inject
     * @var PostRequest
     */
    protected $homePostRequest;

    /**
     * @param $request
     * @return ResponseInterface
     */
    public function post_query($request): ResponseInterface
    {
        $orderBy = $request->input('orderBy', 'id');
        $pageSize = $request->query('pageSize') ?? 12;

        //获取数据
        if ($menu = $request->input('menu', '')) {
            $menu = Category::query()->select('id')->where('value', $menu)->first()['id'];
            $post = Post::query()
                //菜单需要转换为id，单独判断
                ->where('menu', 'like', '%[' . $menu . ',%')
                ->orWhere('menu', 'like', '%,' . $menu . ']%')
                ->orWhere('menu', 'like', ',%,' . $menu . ',%')
                ->orWhere('menu', 'like', '%[' . $menu . ']%')
                ->where($this->postFilter->apply())
                ->orderBy($orderBy, 'desc')
                ->paginate((int)$pageSize, ['*'], 'pageNo');
        } else {
            $post = Post::query()
                //菜单需要转换为id，单独判断
                ->where($this->postFilter->apply())
                ->orderBy($orderBy, 'desc')
                ->paginate((int)$pageSize, ['*'], 'pageNo');
        }

        return $this->success(self::getDisplayColumnData(PostResource::collection($post)->toArray(), $request, $post));
    }

    /**
     * @param $request
     * @return ResponseInterface
     */
    public function post_create($request): ResponseInterface
    {
        //获取验证数据
        $data = self::getValidatedData($request);
        $data['tag'] = json_encode($data['tag']);
        $data['menu'] = json_encode($data['menu']);
        $data['download'] = json_encode($data['download']);
        $data['music'] = !isNull($data['music']) ? json_encode($data['music']) : '[]';
        $data['video'] = !isNull($data['video']) ? json_encode($data['video']) : '[]';
        if ($request->getAttribute('all_permission') === 'all_permission') {
            $data['status'] = 'publish';
        } else {
            $data['status'] = 'draft';
        }

        //创建文章标签
        $tag = \Qiniu\json_decode($data['tag']);
        try {
            if (is_array($tag)) {
                foreach ($tag as $k => $v) {
                    if (Tag::query()->where('label', $v)->get()->count() === 0) {
                        Tag::query()->create([
                            'label' => $v,
                            'value' => $v,
                            'status' => 1,
                        ]);
                    }
                }
            }

            //创建文章
            $flag = Post::query()->create($data)->toArray();

            //转移文件
            if (is_array($data['content_file'])) {
                foreach ($data['content_file'] as $k => $v) {
                    if ($v['filename']) {
                        $data['content_file'][$k] = self::transferFile($flag['id'], $v, 'post_attachment', $data['author']);
                        $path[$k] = $data['content_file'][$k];
                        $data['content'] = preg_replace("/<[img|IMG].*?src=[\'|\"](.*?)\/swap\/" . $v['filename'] . ".*?[\'|\"].*?[\/]?>/", '<img src="${1}/' . $path[$k] . '${2}" style="max-width:100%">', $data['content']);
                    }
                }
            }
            unset($data['content_file']);
            $data['header_img'] = $path[$data['header_img']];

            //更新文章
            $flag = Post::query()->where('id', $flag['id'])->update($data);
            if ($flag) {
                return $this->success();
            }
        } catch (\Throwable $throwable) {
            throw new RequestException($throwable->getMessage(), $throwable->getCode());
        }
        return $this->fail();
    }

    /**
     * @param $request
     * @param JWT $JWT
     * @param $id
     * @return ResponseInterface
     */
    public function post_update($request, JWT $JWT, $id): ResponseInterface
    {
        try {
            //判断是否是JWT用户
            $postAuthorId = Post::query()->select('author')->where('id', $id)->first()->toArray()['author'];
            if (!self::isJWTUser($request, $JWT->getParserData()['id'], $postAuthorId)) {
                return $this->fail([], '用户id错误');
            }
            //获取验证数据
            $data = self::getValidatedData($request);
            $data['tag'] = json_encode($data['tag']);
            $data['menu'] = json_encode($data['menu']);
            $data['download'] = json_encode($data['download']);
            $data['music'] = !isNull($data['music']) ? json_encode($data['music']) : '[]';
            $data['video'] = !isNull($data['video']) ? json_encode($data['video']) : '[]';
            //转移文件
            if (is_array($data['content_file'])) {
                foreach ($data['content_file'] as $k => $v) {
                    if ($v['filename']) {
                        $data['content_file'][$k] = self::transferFile($id, $v, 'post_attachment', $data['author']);
                        $path[$k] = $data['content_file'][$k];
                        $header_img[$k] = $data['content_file'][$k];
                        $data['content'] = preg_replace("/<[img|IMG].*?src=[\'|\"](.*?)\/swap\/" . $v['filename'] . ".*?[\'|\"].*?[\/]?>/", '<img src="${1}/' . $path[$k] . '${2}" style="max-width:100%">', $data['content']);
                    } else {
                        $header_img[$k] = $v['url'];
                    }
                }
            }
            unset($data['content_file']);
            $data['header_img'] = $header_img[$data['header_img']] ?? $data['header_img'];

            //更新文章
            $flag = Post::query()->where('id', $id)->update($data);
            if ($flag) {
                return $this->success();
            }
        } catch (\Throwable $throwable) {
            throw new RequestException($throwable->getMessage(), $throwable->getCode());
        }
        return $this->fail();
    }

    /**
     * @param $request
     * @param $JWT
     * @param $id
     * @return ResponseInterface
     */
    public function post_delete($request, $JWT, $id): ResponseInterface
    {
        try {
            //判断是否是JWT用户
            $postAuthorId = Post::query()->select('author')->where('id', $id)->first()->toArray()['author'];
            if (!self::isJWTUser($request, $JWT->getParserData()['id'], $postAuthorId)) {
                return $this->fail([], '用户id错误');
            }

            //判断是否有评论
            if (Comment::query()->where('post_ID', $id)->first()) {
                return $this->fail([], '文章存在评论');
            }
            $flag = Post::query()->where('id', $id)->delete();
        } catch (\Throwable $throwable) {
            throw new RequestException($throwable->getMessage(), $throwable->getCode());
        }

        //返回结果
        if ($flag) {
            return $this->success();
        }
        return $this->fail();
    }

    /**
     * @param $request
     * @return ResponseInterface
     */
    public function post_purchase($request): ResponseInterface
    {
        $data = $request->all();

        if (isset($data['credit']) && isset($data['download_key']) && isset($data['post_id']) && isset($data['user_id'])) {
            //购买文章
            try {
                //判断积分
                $credit = User::query()->select('credit')->where('id', $data['user_id'])->first()['credit'];
                if ($credit < $data['credit'] || $credit <= 0) {
                    return $this->fail([], '积分不够');
                }

                $flag = Order::query()->insert([
                    'user_id' => $data['user_id'],
                    'post_id' => $data['post_id'],
                    'type' => 'post',
                    'download_key' => $data['download_key'],
                    'credit' => $data['credit'],
                ]);

                //扣取积分
                $credit = $credit - $data['credit'];
                User::query()->where('id', $data['user_id'])->update([
                    'credit' => $credit
                ]);
                if ($flag) {
                    $download = \Qiniu\json_decode(Post::query()->select('download')->where('id', $data['post_id'])->first()->toArray()['download'])[$data['download_key']];
                    return $this->success(['data' => $download]);
                }
            } catch (\Throwable $throwable) {
                throw new RequestException($throwable->getMessage(), $throwable->getCode());
            }
        }
        return $this->fail();
    }
}