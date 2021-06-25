<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Request\UploadRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use League\Flysystem\FileExistsException;
use \League\Flysystem\Filesystem;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Phper666\JWTAuth\Middleware\JWTAuthMiddleware;
use App\Middleware\PermissionMiddleware;
use Psr\Http\Message\ResponseInterface;

/**
 * Class UploadController
 * @package App\Controller\Admin
 * @Controller()
 */
class UploadController extends AbstractController
{
    /**
     * @param UploadRequest $request
     * @param Filesystem $filesystem
     * @return ResponseInterface
     * @throws FileExistsException
     * @RequestMapping(path="uploadAvatar", methods="post")
     * @Middlewares({
     *     @Middleware(JWTAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function uploadAvatar(UploadRequest $request, Filesystem $filesystem): ResponseInterface
    {
        $file = $request->validated();
        $avatar = $file['file'];
        $userId = $file['id'];
        if (!isset($userId)) {
            return $this->fail([], '未选择用户');
        }
        if (!$avatar->isValid()) {
            return $this->fail([], '文件错误');
        }
        //获取扩展名
        $extension = $avatar->getExtension();
        //构建图片链接
        $avatarlink = 'userAvatar/' . $userId . '/' . md5(time() . $avatar->getClientFilename()) . '.' . $extension;
        $filelink = 'uploads/' . $avatarlink;
        $stream = fopen($avatar->getRealPath(), 'r+');
        $filesystem->writeStream(
            $filelink,
            $stream
        );
        fclose($stream);
        return $this->success([
            'link' => $avatarlink,
        ], '上传成功');
    }

    /**
     * @param UploadRequest $request
     * @param Filesystem $filesystem
     * @return ResponseInterface
     * @throws FileExistsException
     * @RequestMapping(path="uploadPostImg", methods="post")
     * @Middlewares({
     *     @Middleware(JWTAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function uploadPostImg(UploadRequest $request, Filesystem $filesystem): ResponseInterface
    {
        $file = $request->validated();
        $postImg = $file['file'];
        $userId = $file['id'];
        if (!isset($userId)) {
            return $this->fail([], '未选择文章');
        }
        if (!$postImg->isValid()) {
            return $this->fail([], '文件错误');
        }
        //获取扩展名
        $extension = $postImg->getExtension();
        //构建图片链接
        $postImglink = 'userPost/' . $userId . '/' . md5(time() . $postImg->getClientFilename()) . '.' . $extension;
        $filelink = 'uploads/' . $postImglink;
        $stream = fopen($postImg->getRealPath(), 'r+');
        $filesystem->writeStream(
            $filelink,
            $stream
        );
        fclose($stream);
        return $this->success([
            'link' => $postImglink,
        ], '上传成功');
    }

    /**
     * @param UploadRequest $request
     * @param Filesystem $filesystem
     * @return ResponseInterface
     * @throws FileExistsException
     * @RequestMapping(path="uploadSiteMeta", methods="post")
     * @Middlewares({
     *     @Middleware(JWTAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     */
    public function uploadSiteMeta(UploadRequest $request, Filesystem $filesystem): ResponseInterface
    {
        $file = $request->validated();
        $postImg = $file['file'];
        if (!$postImg->isValid()) {
            return $this->fail([], '文件错误');
        }
        //获取扩展名
        $extension = $postImg->getExtension();
        //构建图片链接
        $postImglink = 'siteMeta/' . md5(time() . $postImg->getClientFilename()) . '.' . $extension;
        $filelink = 'uploads/' . $postImglink;
        $stream = fopen($postImg->getRealPath(), 'r+');
        $filesystem->writeStream(
            $filelink,
            $stream
        );
        fclose($stream);
        return $this->success([
            'link' => $postImglink,
        ], '上传成功');
    }
}
