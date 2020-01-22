<?php
declare(strict_types=1);

namespace Azonmedia\VueRouter;

use Azonmedia\Utilities\AlphaNumUtil;

/**
 * Class VueRouter
 * @package Azonmedia\VueRouter
 * @see https://router.vuejs.org/
 * This class requires the $router_file argument to be provided - this is where the javascript route array will be dumped
 * when self::dump_routes() is invoked.
 */
class VueRouter
{
    private string $router_file;

    private bool $routes_dumped_flag = FALSE;

    private VueRoute $RootVueRoute;

    /**
     * VueRouter constructor.
     * @param string $router_file Where the routes should be dumped.
     */
    public function __construct(string $router_file)
    {
        $this->router_file = $router_file;
        //$this->RootVueRoute = new VueRoute( NULL, '/', 'root', 'dummy root node' );//this is the route node
        $this->RootVueRoute = new VueRoute( '/', 'root');//this is the route node
    }

    public function __toString()
    {
        return $this->as_string();
    }

    /**
     * Returns a route with $path
     * @param string $path
     * @return VueRouter
     */
    public function __get(string $path) /* mixed */
    {
        if (!isset($this->RootVueRoute->{$path})) {
            throw new \RuntimeException(sprintf('The route %s has no child %s.', $this->RootVueRoute->get_path(), $path));
        }
        return $this->RootVueRoute->{$path};
    }

    /**
     * Checks does the provided path exists
     * @param string $path
     * @return bool
     */
    public function __isset(string $path) : bool
    {
        return isset($this->RootVueRoute->{$path});
    }

    /**
     * Adds a route by setting only the path and component.
     * @param string $path
     * @param string $component
     * @example $VueRouter->{'/some/path'} = '@some.alias/path/to/component.vue'
     */
    public function __set(string $path, /* mixed */ $component) : void
    {
        $this->RootVueRoute->add($path, $component);
    }

    /**
     * Removes a route.
     * @param string $path
     */
    public function __unset(string $path) : void
    {
        unset($this->RootVueRoute->{$path});
    }

    /**
     * Adds a new route.
     * @param string $path Route path
     * @param string $component The component of the route. This would be something like @some.alias/path/to/component.vue
     * @param array $additional see VueRouter::ADDITIONAL_PROPERTIES
     * @param int $order Optional order - if provided the routes will be sorted by this order when dumped
     * @return VueRoute
     */
    public function add(string $path, string $component, array $additional = [], int $order=-1) : VueRoute
    {
        if ($this->are_routes_dumped()) {
            throw new \RuntimeException(sprintf('The routes are already dumped. Adding more routes will have no effect.'));
        }
        return $this->RootVueRoute->add($path, $component, $additional, $order);
    }

    public function remove(string $path) : void
    {
        $this->RootVueRoute->remove($path);
    }

    public function exists(string $path) : bool
    {
        return $this->RootVueRoute->exists($path);
    }

    /**
     * @param string $path
     * @return VueRoute
     */
    public function get(string $path) : VueRoute
    {
        return $this->RootVueRoute->get($path);
    }

    /**
     * Returns the routes as string.
     * @return string
     */
    public function as_string() : string
    {
        //the Vue router expects the first level of nodes to be printed in an array
        $ret = 'export default ['.PHP_EOL;
        $children = $this->RootVueRoute->get_children();
        usort($children, fn(VueRoute $VueRoute1, VueRoute $VueRoute2) : int => $VueRoute1->get_order() <=> $VueRoute2->get_order() );
        foreach ($children as $VueRoute) {
            $ret .= AlphaNumUtil::indent((string) $VueRoute).PHP_EOL;
        }
        $ret .= '];';
        return $ret;
    }

    /**
     * Returns the router file where the routes will be dumped.
     * @return string
     */
    public function get_router_file() : string
    {
        return $this->router_file;
    }

    /**
     * Returns bool are the routes already dumped.
     * @return bool
     */
    public function are_routes_dumped() : bool
    {
        return $this->routes_dumped_flag;
    }

    public function set_routes_dumped(bool $flag) : void
    {
        $this->routes_dumped_flag = $flag;
    }

    /**
     * Dumps the routes to the $router_file provided in the constructor
     */
    public function dump_routes() : void
    {
        $this->set_routes_dumped(TRUE);
        $routes_str = $this->as_string();
        file_put_contents($this->get_router_file(), $routes_str);//replace the old file
    }
}