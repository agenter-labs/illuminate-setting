<?php

namespace AgenterLab\Setting\Middleware;

use Closure;

class LoadSetting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $companyId = request()->query('company_id');

        if ($companyId) {
            // Set the company settings
            setting()->setExtraColumns(['company_id' => $companyId]);
            setting()->load();
        }
        
        return $next($request);
    }
}
