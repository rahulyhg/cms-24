<?php

namespace DaydreamLab\Cms\Requests\Language\Admin;

use DaydreamLab\Cms\Requests\Language\LanguageSearchPost;

class LanguageAdminSearchPost extends LanguageSearchPost
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return parent::authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            //
        ];
        return array_merge($rules, parent::rules());
    }
}
