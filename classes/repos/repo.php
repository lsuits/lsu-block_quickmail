<?php

namespace block_quickmail\repos;

abstract class repo {

    /**
     * Sorts the given collection (by association) by the given "sortable attr" name and direction
     * 
     * @param  array   &$collection
     * @param  string  $attr    sortable attribute name (must be assigned to this repo implementation)
     * @param  string  $dir     asc|desc
     * @return void
     */
    public static function sort_collection(&$collection, $attr, $dir = 'asc') {
        // if sorting by id ascending, stop now for it is likely already done!
        if ($attr == 'id' && $dir == 'asc') {
            return;
        }

        // get the comparisan function that we'll need
        $comp = self::get_comparison($attr, $dir);

        // sort the collection with the comparison function
        usort($collection, $comp);
    }

    /**
     * Returns an anonymous function to compare the given sortable attribute
     * 
     * @param  string  $attr  name of the "sortable attribute" to sort by
     * @param  string  $dir   asc|desc
     * @return callable
     */
    public static function get_comparison($attr, $dir)
    {
        list($key, $type) = self::get_sortable_attr_props($attr);

        $comp_type_method = 'get_comparison_by_type_' . $type;

        return self::$comp_type_method($key, $dir);
    }

    /**
     * Returns an array of properties of the given sortable attribute name
     * 
     * @param  string  $attr  name of the "sortable attribute" to sort by
     * @return array [key, type]
     */
    private static function get_sortable_attr_props($attr)
    {
        $attrs = static::$sortable_attrs;

        return [$attrs[$attr]['key'], $attrs[$attr]['type']];
    }

    /**
     * Returns an anonymous function for sorting by attr type: integer
     * 
     * @param  string  $by  
     * @param  string  $dir  asc|desc
     * @return callable
     */
    public static function get_comparison_by_type_int($by, $dir)
    {
        $comp = function($value1, $value2) use ($by, $dir) {
            $a = $dir == 'desc' ? $value2 : $value1;
            $b = $dir == 'desc' ? $value1 : $value2;

            return (int) $a->get($by) >= (int) $b->get($by);
        };

        return $comp;
    }

    /**
     * Returns an anonymous function for sorting by attr type: string
     * 
     * @param  string  $by  
     * @param  string  $dir  asc|desc
     * @return callable
     */
    public static function get_comparison_by_type_string($by, $dir)
    {
        $comp = function($value1, $value2) use ($by, $dir) {
            $a = $dir == 'desc' ? $value2 : $value1;
            $b = $dir == 'desc' ? $value1 : $value2;

            return strcmp($a->get($by), $b->get($by));
        };

        return $comp;
    }

}