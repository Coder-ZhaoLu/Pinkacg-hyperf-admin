<?php

declare(strict_types=1);

namespace App\Request\admin;

use Hyperf\Validation\Request\FormRequest;

class AttachmentCatRequest extends FormRequest
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
            'name' => 'required|string|max:25|min:4',
            'slug' => 'regex:/^[\w-]*$/|max:25|min:4',
            'status' => 'required|boolean'
        ];
    }

    /**
     * @return string[]
     * 获取已定义验证规则的错误消息
     */
    public function messages(): array
    {
        return [
            'name:required' => '请输入分类名称',
            'slug:required' => '请输入分类标识',
            'status:required' => '请输入分类状态'
        ];
    }
}
