<?php


namespace App\Services;


use App\Exception\RequestException;
use App\Filters\CommentFilter;
use App\Model\Comment;
use App\Model\Post;
use App\Resource\admin\CommentResource;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;

class CommentService extends Service
{
    /**
     * @Inject
     * @var CommentFilter
     */
    protected $commentFilter;

    /**
     * @param $request
     * @return ResponseInterface
     */
    public function comment_query($request): ResponseInterface
    {
        $orderBy = $request->input('orderBy', 'id');
        $pageSize = $request->query('pageSize') ?? 12;

        //获取数据
        try {
            $comment = Comment::query()
                ->where($this->commentFilter->apply())
                ->orderBy($orderBy, 'asc')
                ->paginate((int)$pageSize, ['*'], 'pageNo');
            return $this->success(self::getDisplayColumnData(CommentResource::collection($comment)->toArray(), $request, $comment));
        } catch (\Throwable $throwable) {
            throw new RequestException($throwable->getMessage(), $throwable->getCode());
        }
    }

    /**
     * @param $request
     * @return ResponseInterface
     */
    public function comment_create($request): ResponseInterface
    {
        //获取验证数据
        $data = self::getValidatedData($request);

        //创建内容
        try {
            $flag = Comment::query()->create($data);
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
     * @param $JWT
     * @param $id
     * @return ResponseInterface
     */
    public function comment_update($request, $JWT, $id): ResponseInterface
    {
        //获取作者id
        try {
            $postAuthorId = Post::query()->select('author')->where('id', $id)->first()->toArray()['author'];
        } catch (\Throwable $throwable) {
            throw new RequestException($throwable->getMessage(), $throwable->getCode());
        }
        //判断是否是JWT用户
        if (!self::isJWTUser($request, $JWT->getParserData()['id'], $postAuthorId)) {
            return $this->fail([], '用户id错误');
        }

        //获取验证数据
        $data = self::getValidatedData($request);

        //更新内容
        try {
            $flag = Comment::query()->where('id', $id)->update($data);
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
     * @param $JWT
     * @param $id
     * @return ResponseInterface
     */
    public function comment_delete($request, $JWT, $id): ResponseInterface
    {
        //获取作者id
        try {
            $postAuthorId = Post::query()->select('author')->where('id', $id)->first()->toArray()['author'];
        } catch (\Throwable $throwable) {
            throw new RequestException($throwable->getMessage(), $throwable->getCode());
        }
        //判断是否是JWT用户
        if (!self::isJWTUser($request, $JWT->getParserData()['id'], $postAuthorId)) {
            return $this->fail([], '用户id错误');
        }

        //删除内容
        try {
            $flag = Comment::query()->where('id', $id)->delete();
        } catch (\Throwable $throwable) {
            throw new RequestException($throwable->getMessage(), $throwable->getCode());
        }

        //返回结果
        if ($flag) {
            return $this->success();
        }
        return $this->fail();
    }

}