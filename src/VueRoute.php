<?php

namespace Azonmedia\VueRouter;

use Azonmedia\Utilities\AlphaNumUtil;

/**
 * Class VueRoute
 * @package Azonmedia\VueRouter
 * @see https://router.vuejs.org/
 * For reference on the properties @see https://router.vuejs.org/api/#routes
 * Provides array overloading for accessing the properties of the route
 * @example
 * print $VueRoute['component']
 * Provides $order property which is not part of the Vue route specification but is used for ordering the child nodes.
 * By default the child nodes are ordered in the order they are added.
 *
 * TODO: add support for components, redirect, alias, beforeEnter, caseSensitive, pathToRegexpOptions
 * TODO: add support for bundling routes
 * TODO: set default types for ADDITIONAL_PROPERTIES - convert this to associative array name=>type
 */
class VueRoute implements \ArrayAccess
{

    /**
     * @var VueRouter[]
     */
    private array $children = [];

    private string $path;

    private string $component;

    private array $additional = [];

    /**
     * @var int
     */
    private int $order = -1;

    /**
     * These are not overloaded.
     */
    private const ROUTE_PROPERTIES = ['path', 'component', 'additional', 'order'];

    public const ADDITIONAL_PROPERTIES = ['name', 'meta', 'props', 'components','redirect','alias','beforeEnter','caseSensitive','pathToRegexpOptions'];

    /**
     * VueRoute constructor.
     * @param string $path
     * @param string $component
     * @param array $additional See self::ADDITIONAL_PROPERTIES
     * @param int $order
     */
    public function __construct(string $path, string $component, array $additional = [], int $order = -1)
    {
        if (!empty($additional['meta']['in_navigation']) && empty($additional['name'])) {
            throw new \InvalidArgumentException(sprintf('A route that is to be shown in the navigation requires the "name" argument to be provided.'));
        }

        if (!$path) {
            throw new \InvalidArgumentException(sprintf('No path is provided.'));
        }
        if (!$component) {
            throw new \InvalidArgumentException(sprintf('No component provided.'));
        }
        foreach ($additional as $key=>$value) {
            if (!in_array($key, self::ADDITIONAL_PROPERTIES)) {
                throw new \InvalidArgumentException(sprintf('The additional properties contain an unsupported key/property %s.', $key));
            }
        }

        $this->path = $path;
        $this->component = $component;

        $this->additional = $additional;
        $this->order = $order;
    }

    public function __toString() : string
    {
        return $this->as_string();
    }

    /**
     * Returns child VueRoute with the given $path if exists
     * @param string $path
     * @return VueRouter
     * @throws \RuntimeException If there is no such child
     */
    public function __get(string $path) /* mixed */
    {
        return $this->get($path);
    }

    /**
     * Checks does a child route with $path exists.
     * @param string $path
     * @return bool
     */
    public function __isset(string $path) : bool
    {
        return $this->exists($path);
    }

    /**
     * Adds a child route with $path and handled by $component
     * @param string $path
     * @param string $component
     * @throws \RuntimeException If there is already a child with the given $path
     */
    public function __set(string $path, /* mixed */ $component) : void
    {
        $this->add($path, $component);
    }

    /**
     * @param string $path
     */
    public function __unset(string $path) : void
    {
        $this->remove($path);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return in_array($offset, self::ROUTE_PROPERTIES) || in_array($offset, self::ADDITIONAL_PROPERTIES);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException(sprintf('The VueRoute have no property %s.', $offset));
        }
        $ret = NULL;
        if (in_array($offset, self::ADDITIONAL_PROPERTIES)) {
            $ret = $this->additional[$offset] ?? NULL;
        } else {
            $ret = $this->{$offset};
        }
        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException(sprintf('The VueRoute have no property %s.', $offset));
        }
        if (in_array($offset, self::ADDITIONAL_PROPERTIES)) {
            $this->additional['$offset'] = $value;
        } else {
            $this->{$offset} = $value;
        }

    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException(sprintf('Properties on VueRoute can not be unset.'));
    }

    /**
     * Adds a new child route
     * @param string $path
     * @param string $component
     * @param array $additional See self::ADDITIONAL_PROPERTIES
     * @param int $order
     * @return $this
     * @throws \RuntimeException If there is already a child route with the given $path
     */
    public function add(string $path, string $component, array $additional = [], int $order=-1) : self
    {
        if (isset($this->{$path})) {
            throw new \RuntimeException(sprintf('There is already a child path %s to %s.', $path, $this->path));
        }
        $this->children[$path] =  new self($path, $component, $additional, $order);
        return $this->children[$path];
    }

    /**
     * Removes the child route with $path
     * @param string $path
     * @throws \RuntimeException If there is no child route with the given $path
     */
    public function remove(string $path) : void
    {
        if (!$this->exists($path)) {
            throw new \RuntimeException(sprintf('The provided child path %s does not exist on path %s (component %s).', $path, $this->get_path(), $this->get_component() ));
        }
        unset($this->children[$path]);
    }

    /**
     * Returns true if there is a child route with $path
     * @param string $path
     * @return bool
     */
    public function exists(string $path) : bool
    {
        return array_key_exists($path, $this->children);
    }

    /**
     * Returns the child route with $path
     * @param string $path
     * @return VueRoute
     * @throws \RuntimeException if there is no child route with the given $path
     */
    public function get(string $path) : VueRoute
    {
        if (!$this->exists($path)) {
            throw new \RuntimeException(sprintf('The provided child path %s does not exist on path %s (component %s).', $path, $this->get_path(), $this->get_component() ));
        }
        return $this->children[$path];
    }

    /**
     * Returns this route as sting.
     * @return string
     */
    public function as_string() : string
    {
        if (!empty($this->additional['meta'])) {
            $meta_str = PHP_EOL.'meta: {'.PHP_EOL;
            foreach ($this->additional['meta'] as $key=>$value) {
                $meta_str .= "'$key': '$value',".PHP_EOL;
            }
            $meta_str .= '}';
        } else {
            $meta_str = '';
        }
        if (!empty($this->additional['props'])) {
            $props_str = PHP_EOL.'props: {'.PHP_EOL;
            foreach ($this->additional['props'] as $key=>$value) {
                $props_str .= "'$key': '$value',".PHP_EOL;
            }
            $props_str .= '}';
        } else {
            $props_str = '';
        }

        if ($this->children) {
            $children = $this->children;
            usort($children, fn(VueRoute $VueRoute1, VueRoute $VueRoute2) : int => $VueRoute1->get_order() < $VueRoute2->get_order() ? -1 : 1 );
            $children_str = PHP_EOL.'children: ['.PHP_EOL;
            foreach ($children as $VueRoute) {
                $children_str .= AlphaNumUtil::indent($VueRoute).PHP_EOL;
            }
            $children_str .= ']';
            $children_str = AlphaNumUtil::indent($children_str);
        } else {
            $children_str = '';
        }
        $name = $this->additional['name'] ?? '';
        $route_str = <<<ROUTE
{
    path: '{$this->path}',
    name: '{$name}',
    component: () => import('{$this->component}'),{$meta_str}{$props_str}{$children_str}
},
ROUTE;
        return $route_str;
    }

    /**
     * Returns the path of the route.
     * @return string
     */
    public function get_path() : string
    {
        return $this->path;
    }

    /**
     * Returns the component of the route.
     * @return string
     */
    public function get_component() : string
    {
        return $this->component;
    }

    /**
     * Returns the name of the route
     * @return string
     */
    public function get_name() : string
    {
        return $this->additional['name'] ?? '';
    }

    /**
     * Returns the props array
     * @return array
     */
    public function get_props() : array
    {
        return $this->additional['props'] ?? [];
    }

    /**
     * Returns the meta array
     * @return array
     */
    public function get_meta() : array
    {
        return $this->additional['meta'] ?? [];
    }

    /**
     * Returns the position ($order argument) of the node.
     * @return int
     */
    public function get_order() : int
    {
        return $this->order;
    }

    /**
     * Returns all children (ordered in the order they were added).
     * @return array
     */
    public function get_children() : array
    {
        return $this->children;
    }
}