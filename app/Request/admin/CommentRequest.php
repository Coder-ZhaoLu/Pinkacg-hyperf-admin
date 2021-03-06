<?php

declare(strict_types=1);

namespace App\Request\admin;

use Hyperf\Validation\Request\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'post_ID' => 'required|integer|exists:posts,id',
            'content' => 'required|string|max:200|min:2',
            'parent' => 'required|integer',
            'user_id' => 'required|integer|exists:users,id',
            'like' => 'required|integer',
            'status' => 'required|boolean',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息
     */
    public function messages(): array
    {
        return [
            'post_ID:required' => '请输入文章id！',
            'content:required' => '请输入内容！',
//            'type:required' => '请输入类型！',
            'parent:required' => '请输入父评论！',
            'user_id:required' => '请输入用户id！',
            'like:required' => '请输入喜欢数！',
            'status:required' => (int) '请输入状态！',
        ];
    }
}
