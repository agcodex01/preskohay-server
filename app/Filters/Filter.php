<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Filter
{

    /**
     *  The request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     *  The builder instance
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     *  Apply the filter in the builder
     *
     * @param \Illuminate\Database\Eloquent\Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $key => $value) {
            if (method_exists($this, $key)) {
                call_user_func_array([$this, $key], array_filter([$value]));
            }
        }

        return $this->builder;
    }
}
