<?php


namespace App\Services;


use App\Filters\MenuFilter;
use App\Model\AdminPermission;
use App\Resource\admin\MenuPermissionResource;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;

class MenuPermissionService extends Service
{
    /**
     * @Inject
     * @var MenuFilter
     */
    protected $menuFilter;

    /**
     * @param $request
     * @return ResponseInterface
     */
    public function index($request): ResponseInterface
    {
        $orderBy = $request->input('orderBy', 'id');
        $pageSize = $request->query('pageSize') ?? 1000;
        $pageNo = $request->query('pageNo') ?? 1;

        $menu = AdminPermission::query()
            ->where($this->menuFilter->apply())
            ->orderBy($orderBy, 'asc')
            ->paginate((int)$pageSize, ['*'], 'page', (int)$pageNo);
        $menus = $menu->toArray();

        return $this->success([
            'pageSize' => $menus['per_page'],
            'pageNo' => $menus['current_page'],
            'totalCount' => $menus['total'],
            'totalPage' => $menus['to'],
            'data' => self::getDisplayColumnData(MenuPermissionResource::collection($menu)->toArray(), $request),
        ]);
    }

    /**
     * @param $request
     * @return ResponseInterface
     */
    public function create($request): ResponseInterface
    {
        //获取验证数据
        $data = self::getValidatedData($request);

        $flag = AdminPermission::query()->create($data);
        if ($flag) {
            return $this->success();
        }
        return $this->fail();
    }

    /**
     * @param $request
     * @param $id
     * @return ResponseInterface
     */
    public function update($request, $id): ResponseInterface
    {
        //获取验证数据
        $data = self::getValidatedData($request);

        $flag = AdminPermission::query()->where('id', $id)->update($data);
        if ($flag) {
            return $this->success();
        }
        return $this->fail();
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id): ResponseInterface
    {
        //存在子菜单
        if (AdminPermission::query()->where('p_id', $id)) {
            return $this->fail([], '存在子菜单');
        }
        //有用户选择此菜单
        if (AdminPermission::query()->where('id', $id)->delete()) {
            return $this->success();
        }
        return $this->fail();
    }
}