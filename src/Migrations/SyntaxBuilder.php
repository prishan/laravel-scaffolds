<?php

namespace Prishan\LaravelScaffolds\Migrations;

use Prishan\LaravelScaffolds\GeneratorException;


/**
 * Class SyntaxBuilder with modifications by Fernando
 * @package Prishan\LaravelScaffolds\Migrations
 * @author Jeffrey Way <jeffrey@jeffrey-way.com>
 */
class SyntaxBuilder
{

    /**
     * A template to be inserted.
     *
     * @var string
     */
    private $template;

    /**
     * Create the PHP syntax for the given schema.
     *
     * @param  array $schema
     * @param  array $meta
     * @param  string $type
     * @return string
     * @throws GeneratorException
     */
    public function create($schema, $meta, $type = "migration")
    {
        if ($type == "migration") {

            $up = $this->createSchemaForUpMethod($schema, $meta);
            $down = $this->createSchemaForDownMethod($schema, $meta);
            return compact('up', 'down');


        } else if ($type == "controller") {

            $fieldsc = $this->createSchemaForControllerMethod($schema, $meta);
            return $fieldsc;


        } else if ($type == "view-index-header") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'index-header');
            return $fieldsc;

        } else if ($type == "view-index-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'index-content');
            return $fieldsc;

        } else if ($type == "view-show-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'show-content');
            return $fieldsc;

        } else if ($type == "view-edit-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'edit-content');
            return $fieldsc;

        } else if ($type == "view-create-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'create-content');
            return $fieldsc;
            
        } else if ($type == 'filter-index-fields') {
            
            $fieldsc = $this->createSchemaForFilterMethod($schema, $meta, 'index-content');
            return $fieldsc;
            
        } else if ($type == 'grid-index-fields') {
            
            $fieldsc = $this->createSchemaForGridMethod($schema, $meta, 'index-content');
            return $fieldsc;
            
        } else {
            throw new \Exception("Type not found in syntaxBuilder");
        }
    }

    /**
     * Create the schema for the "up" method.
     *
     * @param  string $schema
     * @param  array $meta
     * @return string
     * @throws GeneratorException
     */
    private function createSchemaForUpMethod($schema, $meta)
    {
        //dd($schema);
        $fields = $this->constructSchema($schema);


        if ($meta['action'] == 'create') {
            return $this->insert($fields)->into($this->getCreateSchemaWrapper());
        }

        if ($meta['action'] == 'add') {
            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        if ($meta['action'] == 'remove') {
            $fields = $this->constructSchema($schema, 'Drop');

            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        // Otherwise, we have no idea how to proceed.
        throw new GeneratorException;
    }


    /**
     * Construct the syntax for a down field.
     *
     * @param  array $schema
     * @param  array $meta
     * @return string
     * @throws GeneratorException
     */
    private function createSchemaForDownMethod($schema, $meta)
    {
        // If the user created a table, then for the down
        // method, we should drop it.
        if ($meta['action'] == 'create') {
            return sprintf("Schema::drop('%s');", $meta['table']);
        }

        // If the user added columns to a table, then for
        // the down method, we should remove them.
        if ($meta['action'] == 'add') {
            $fields = $this->constructSchema($schema, 'Drop');

            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        // If the user removed columns from a table, then for
        // the down method, we should add them back on.
        if ($meta['action'] == 'remove') {
            $fields = $this->constructSchema($schema);

            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        // Otherwise, we have no idea how to proceed.
        throw new GeneratorException;
    }

    /**
     * Store the given template, to be inserted somewhere.
     *
     * @param  string $template
     * @return $this
     */
    private function insert($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get the stored template, and insert into the given wrapper.
     *
     * @param  string $wrapper
     * @param  string $placeholder
     * @return mixed
     */
    private function into($wrapper, $placeholder = 'schema_up')
    {
        return str_replace('{{' . $placeholder . '}}', $this->template, $wrapper);
    }

    /**
     * Get the wrapper template for a "create" action.
     *
     * @return string
     */
    private function getCreateSchemaWrapper()
    {
        return file_get_contents(__DIR__ . '/../stubs/schema-create.stub');
    }

    /**
     * Get the wrapper template for an "add" action.
     *
     * @return string
     */
    private function getChangeSchemaWrapper()
    {
        return file_get_contents(__DIR__ . '/../stubs/schema-change.stub');
    }

    /**
     * Construct the schema fields.
     *
     * @param  array $schema
     * @param  string $direction
     * @return array
     */
    private function constructSchema($schema, $direction = 'Add')
    {
        if (!$schema) return '';

        $fields = array_map(function ($field) use ($direction) {
            $method = "{$direction}Column";
            return $this->$method($field);
        }, $schema);


        return implode("\n" . str_repeat(' ', 12), $fields);
    }


    /**
     * Construct the syntax to add a column.
     *
     * @param  string $field
     * @param string $type
     * @param $meta
     * @return string
     */
    private function addColumn($field, $type = "migration", $meta = "")
    {

        if ($type == 'migration') {

            $syntax = sprintf("\$table->%s('%s')", $field['type'], $field['name']);

            // If there are arguments for the schema type, like decimal('amount', 5, 2)
            // then we have to remember to work those in.
            if ($field['arguments']) {
                $syntax = substr($syntax, 0, -1) . ', ';

                $syntax .= implode(', ', $field['arguments']) . ')';
            }

            foreach ($field['options'] as $method => $value) {
                $syntax .= sprintf("->%s(%s)", $method, $value === true ? '' : $value);
            }

            $syntax .= ';';


        } elseif ($type == 'view-index-header') {

            // Fields to index view
            $syntax = sprintf("<th>%s", strtoupper($field['name']));
            $syntax .= '</th>';

        } elseif ($type == 'view-index-content') {

            // Fields to index view
            $syntax = sprintf("<td>{{\$%s->%s", $meta['var_name'], strtolower($field['name']));
            $syntax .= '}}</td>';

        } elseif ($type == 'view-show-content') {

            // Fields to show view
            $syntax = sprintf("<tr class=\"{{row_class}}\">\n" .
                str_repeat(' ', 21)."<th class=\"show-view-th\">%s</th>\n" .
                str_repeat(' ', 21)."<td>{{\$%s->%s}}</td>\n" .
                str_repeat(' ', 16)."</tr>", ucwords(str_replace('_', ' ', $field['name'])), $meta['var_name'], strtolower($field['name']));


        } elseif ($type == 'view-edit-content') {
            if (trim($field["type"]) == "text") {
                $content = "<textarea name=\"%s\" class=\"form-control\">{{ \App\Libs\ValueHelper::getOldInput(\$%s,'%s') }}</textarea>\n";
            } else {
                $content = "<input type=\"text\" name=\"%s\" class=\"form-control\" value=\"{{ \App\Libs\ValueHelper::getOldInput(\$%s,'%s') }}\"/>\n";
            }
            // Fields to show view
            $syntax = sprintf("<div class=\"form-group\">\n" .
                str_repeat(' ', 21)."<label for=\"%s\">%s</label>\n" .
                str_repeat(' ', 21). $content .
                str_repeat(' ', 21)."{!! \App\Libs\ErrorDisplay::getInstance()->displayIndividual(\$errors, \"%s\") !!}\n" .
                str_repeat(' ', 16)."</div>", strtolower($field['name']), strtoupper($field['name']), strtolower($field['name']), $meta['var_name'], strtolower($field['name']), strtolower($field['name']));


        } elseif ($type == 'view-create-content') {
            if (trim($field["type"]) == "text") {
                $content = "<textarea name=\"%s\" class=\"form-control\">{{ Session::getOldInput('%s') }}</textarea>\n";
            } else {
                $content = "<input type=\"text\" name=\"%s\" class=\"form-control\" value=\"{{  Session::getOldInput('%s') }}\"/>\n";
            }
            // Fields to show view
            $syntax = sprintf("<div class=\"form-group\">\n" .
                str_repeat(' ', 21)."<label for=\"%s\">%s</label>\n" .
                str_repeat(' ', 21). $content .
                str_repeat(' ', 21)."{!! \App\Libs\ErrorDisplay::getInstance()->displayIndividual(\$errors, \"%s\") !!}\n" .
                str_repeat(' ', 16)."</div>", strtolower($field['name']), strtoupper($field['name']), strtolower($field['name']), strtolower($field['name']), strtolower($field['name']));
        
            
        } elseif ($type == 'filter-index-content') {
            
            if (in_array(trim($field["type"]),["string","text","char","mediumText"])) {// string & text
                $syntax = sprintf('$filter->add(\'%s\',\'%s\', \'text\');',strtolower($field['name']),ucfirst($field['name']));
            } elseif (in_array(trim($field["type"]),["decimal","double","float","integer","smallInteger","tinyInteger",])) {// numbers
                $syntax = sprintf('$filter->add(\'%s\',\'%s\', \'text\')->clause(\'where\');',strtolower($field['name']),ucfirst($field['name']));
            } elseif (in_array(trim($field["type"]),["date","dateTime","time","timestamp"])) {// numbers
                $syntax = sprintf('$filter->add(\'%s\',\'%s\', \'daterange\');',strtolower($field['name']),ucfirst($field['name']));
            } else {
                return '';
            }
            
        } elseif ($type == 'grid-index-content') {
            
            if (in_array(trim($field["type"]),["string","char",])) { //string
                $syntax = sprintf('$grid->add(\'%s\',\'%s\', true);',strtolower($field['name']),ucfirst($field['name']));
            } elseif (in_array(trim($field["type"]),["decimal","double","float","integer","smallInteger","tinyInteger",])) {//numbers
                $syntax = sprintf('$grid->add(\'%s\',\'%s\', true);',strtolower($field['name']),ucfirst($field['name']));
            } elseif (in_array(trim($field["type"]),["text","mediumText",])) {// text
                $syntax = sprintf('$grid->add(\'{{ substr($%s,0,20) }}...\',\'%s\');',strtolower($field['name']),ucfirst($field['name']));
            } elseif (in_array(trim($field["type"]),["date","dateTime","time","timestamp"])) {// date
                $syntax = sprintf('$grid->add(\'%s\',\'%s\', true);',strtolower($field['name']),ucfirst($field['name']));
            } else {
                return '';
            }
            
        } else {

            // Fields to controller
            $syntax = sprintf("\$%s->%s = \$request->input(\"%s", $meta['var_name'], $field['name'], $field['name']);
            $syntax .= '");';
        }


        return $syntax;
    }


    /**
     * Construct the syntax to drop a column.
     *
     * @param  string $field
     * @return string
     */
    private function dropColumn($field)
    {
        return sprintf("\$table->dropColumn('%s');", $field['name']);
    }


    /**
     * Construct the controller fields
     *
     * @param $schema
     * @param $meta
     * @return string
     */
    private function createSchemaForControllerMethod($schema, $meta)
    {


        if (!$schema) return '';

        $fields = array_map(function ($field) use ($meta) {
            return $this->AddColumn($field, 'controller', $meta);
        }, $schema);


        return implode("\n" . str_repeat(' ', 8), $fields);
    }


    /**
     * Construct the view fields
     *
     * @param $schema
     * @param $meta
     * @param string $type Params 'header' or 'content'
     * @return string
     */
    private function createSchemaForViewMethod($schema, $meta, $type = 'index-header')
    {


        if (!$schema) return '';

        $fields = array_map(function ($field) use ($meta, $type) {
            return $this->AddColumn($field, 'view-' . $type, $meta);
        }, $schema);
        
        // set odd and even rows
        array_walk($fields, function(&$field, $key){
            $field = str_replace('{{row_class}}', ($key%2==0?'even':'odd'), $field);
        });

        // Format code
        if ($type == 'index-header') {
            return implode("\n" . str_repeat(' ', 24), $fields);
        } else {
            // index-content
            return implode("\n" . str_repeat(' ', 20), $fields);
        }

    }

    /**
     * Construct the filter fields
     *
     * @param $schema
     * @param $meta
     * @param string $type Params 'index' or ''
     * @return string
     */
    private function createSchemaForFilterMethod($schema, $meta, $type = 'index-content')
    {

        if (!$schema) return '';

        $fields = array_map(function ($field) use ($meta, $type) {
            return $this->AddColumn($field, 'filter-' . $type, $meta);
        }, $schema);
        $fields = $this->cleanFields($fields);

        // Format code
        if ($type == 'index-content') {
            return implode("\n" . str_repeat(' ', 16), $fields);
        } else {
            return '';
        }

    }

    /**
     * Construct the Grid fields
     *
     * @param $schema
     * @param $meta
     * @param string $type Params 'index' or ''
     * @return string
     */
    private function createSchemaForGridMethod($schema, $meta, $type = 'index-content')
    {

        if (!$schema) return '';

        $fields = array_map(function ($field) use ($meta, $type) {
            return $this->AddColumn($field, 'grid-' . $type, $meta);
        }, $schema);
        $fields = $this->cleanFields($fields);
        
        // Format code
        if ($type == 'index-content') {
            return implode("\n" . str_repeat(' ', 16), $fields);
        } else {
            return '';
        }

    }
    
    /**
     * Remove the empty fields from the fields array
     * Comments additional fields
     * 
     * @param array $fields
     * @param integer $commentAfter Use to determine the limit to comment from
     * @return array Cleaned array
     */
    private function cleanFields(array $fields, $commentAfter = 4)
    {
        $fields = array_values(array_filter($fields));
        array_walk($fields, function(&$field, $fieldNo, $commentAfter){
            $field = ($fieldNo>=$commentAfter)?'// '.$field:$field;
        }, $commentAfter);
        
        return $fields;
    }

}
