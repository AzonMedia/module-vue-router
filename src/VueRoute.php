<?php

namespace Azonmedia\VueRouter;


use Azonmedia\Utilities\AlphaNumUtil;

class VueRoute implements \ArrayAccess
{

    //private ?VueRoute $ParentVueRoute = NULL;

    /**
     * @var VueRouter[]
     */
    private array $children = [];

    private string $path;

    private array $props = [];

    private array $meta = [];

    private string $component;

    private string $name = '';

    private int $order = -1;

    private const ROUTE_PROPERTIES = ['path', 'props', 'meta', 'component', 'name', 'order'];

    //public function __construct(?VueRoute $ParentVueRoute, string $path, string $component, string $name = '', array $meta = [], array $props = [], int $order = -1)
    public function __construct(string $path, string $component, string $name = '', array $meta = [], array $props = [], int $order = -1)
    {
        if (!empty($meta['in_navigation']) && !$name) {
            throw new \InvalidArgumentException(sprintf('A route that is to be shown in the navigation requires the "name" argument to be provided.'));
        }

        if (!$path) {
            throw new \InvalidArgumentException(sprintf('No path is provided.'));
        }
//        if ($path[0]==='/' && $ParentVueRoute && $ParentVueRoute->get_parent()) { //the nodes under the dummy route can start with /
//            throw new \InvalidArgumentException(sprintf('A child path can not start with "/".'));
//        }
        if (!$component) {
            throw new \InvalidArgumentException(sprintf('No component provided.'));
        }

        //$this->ParentVueRoute = $ParentVueRoute;
        $this->path = $path;
        $this->component = $component;
        $this->name = $name;
        $this->meta = $meta;
        $this->props = $props;
        $this->order = $order;
    }

    public function as_string() : string
    {
        if ($this->meta) {
            $meta_str = PHP_EOL.'meta: {'.PHP_EOL;
            foreach ($this->meta as $key=>$value) {
                $meta_str .= "'$key': '$value',".PHP_EOL;
            }
            $meta_str .= '}';
        } else {
            $meta_str = '';
        }
        if ($this->props) {
            $props_str = PHP_EOL.'props: {'.PHP_EOL;
            foreach ($this->props as $key=>$value) {
                $props_str .= "'$key': '$value',".PHP_EOL;
            }
            $props_str .= '}';
        } else {
            $props_str = '';
        }

        if ($this->children) {
            $children_str = PHP_EOL.'children: ['.PHP_EOL;
            foreach ($this->children as $VueRoute) {
                $children_str .= $VueRoute.PHP_EOL;
            }
            $children_str .= ']';
            $children_str = AlphaNumUtil::indent($children_str);
        } else {
            $children_str = '';
        }
        $route_str = <<<ROUTE
{
    path: '{$this->path}',
    name: '{$this->name}',
    component: () => import('{$this->component}'),{$meta_str}{$props_str}{$children_str}
},
ROUTE;
        return $route_str;
    }

    public function __toString() : string
    {
        return $this->as_string();
    }

    public function __get(string $property) /* mixed */
    {
        if (!isset($this->{$property})) {
            throw new \RuntimeException(sprintf('The route %s has no child %s.', $this->path, $property));
        }
        return $this->{$property};
    }

    public function __isset(string $property) : bool
    {
        return array_key_exists($property, $this->children);
    }

    public function __set(string $property, /* mixed */ $value) : void
    {
        $this->add($property, $value);
    }

    public function __unset(string $property) : void
    {
        unset($this->children[$property]);
    }

    public function add(string $path, string $component, string $name = '', array $meta = [], array $props = [], int $order=-1) : self
    {
        if (isset($this->{$path})) {
            throw new \RuntimeException(sprintf('There is already a child path %s to %s.', $path, $this->path));
        }
        //$this->children[$path] =  new self($this, $path, $component, $name, $meta, $props, $order);
        $this->children[$path] =  new self($path, $component, $name, $meta, $props, $order);
        return $this->children[$path];
    }


    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return in_array($offset, self::ROUTE_PROPERTIES);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException(sprintf('The VueRoute have no property %s.', $offset));
        }
        return $this->{$offset};
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException(sprintf('The VueRoute have no property %s.', $offset));
        }
        $this->{$offset} = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException(sprintf('Properties on VueRoute can not be unset.'));
    }

    public function get_path() : string
    {
        return $this->path;
    }

    public function get_component() : string
    {
        return $this->component;
    }

    public function get_name() : string
    {
        return $this->name;
    }

    public function get_props() : array
    {
        return $this->props;
    }

    public function get_meta() : array
    {
        return $this->meta;
    }

    public function get_children() : array
    {
        return $this->children;
    }

//    public function get_parent() : ?VueRoute
//    {
//        return $this->ParentVueRoute;
//    }
}