<?php


namespace Azonmedia\VueRouter;


class VueRouter
{
    private string $router_file;

    private bool $routes_dumped_flag = FALSE;

    private VueRoute $RootVueRoute;

    public function __construct(string $router_file)
    {
        $this->router_file = $router_file;
        //$this->RootVueRoute = new VueRoute( NULL, '/', 'root', 'dummy root node' );//this is the route node
        $this->RootVueRoute = new VueRoute( '/', 'root', 'dummy root node' );//this is the route node
    }

    public function __toString()
    {
        return $this->as_string();
    }

    public function __get(string $property) /* mixed */
    {
        if (!isset($this->RootVueRoute->{$property})) {
            throw new \RuntimeException(sprintf('The route %s has no child %s.', $this->path, $property));
        }
        return $this->RootVueRoute->{$property};
    }

    public function __isset(string $property) : bool
    {
        return isset($this->RootVueRoute->{$property});
    }

    public function __set(string $property, /* mixed */ $value) : void
    {
        $this->RootVueRoute->add($property, $value);
    }

    public function __unset(string $property) : void
    {
        unset($this->RootVueRoute->{$property});
    }

    public function as_string() : string
    {
        return (string) $this->RootVueRoute;
    }

    public function get_router_file() : string
    {
        return $this->router_file;
    }

    public function are_routes_dumped() : bool
    {
        return $this->routes_dumped_flag;
    }

    public function dump_routes() : void
    {

    }
}