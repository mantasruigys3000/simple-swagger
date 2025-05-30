<?php

namespace Mantasruigys3000\SimpleSwagger\Helpers;

use Illuminate\Routing\Route as RouteData;
use Illuminate\Support\Facades\Route;

class RouteHelper
{
    /**
     * @return RouteData[]
     */
    public static function getRoutes() : array
    {
        $routes = Route::getRoutes()->getRoutes();

        // Filter out unwanted routes
        $routes = array_filter($routes, function (RouteData $route) {

            // Look through every allowed route, if any accept they allow the route
            foreach (config('docs.allowed_routes') as $allowedRoute) {
                if (fnmatch($allowedRoute, $route->uri)) {
                    return true;
                }
            }

            return false;
        });

        return $routes;
    }
}