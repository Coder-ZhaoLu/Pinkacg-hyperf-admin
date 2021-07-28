<?php

declare(strict_types=1);

namespace App\Request\admin;

use Hyperf\Validation\Request\FormRequest;

class UserRequest extends FormRequest
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
            'avatar' => 'required',
            'background' => 'required',
            'check' => 'required|boolean',
            'email' => 'required|email',
            'name' => 'required|string|min:2|max:100',
            'desc' => 'required|string|min:2|max:200',
            'password' => 'required',
            'telephone' => 'required|integer',
            'answertest' => 'required|integer',
            'username' => 'required|string|min:2|max:100',
            'user_role' => 'required|integer'
        ];
    }

    /**
     * 获取已定义验证规则的错误消息
     */
    public function messages(): array
    {
        return [
            'check.required' => '请输入状态！',
            'email.required'  => '请输入邮箱！',
            'name.required'  => '请输入昵称！',
            'desc.required'  => '请输入描述！',
            'avatar.required'  => '请输入头像！',
            'background.required'  => '请输入背景！',
            'password.required'  => '请输入密码！',
            'telephone.required'  => '请输入电话！',
            'answertest.required'  => '请输入答题得分！',
            'username.required'  => '请输入用户名！',
            'user_role.required'  => '请输入角色！',
        ];
    }
}
