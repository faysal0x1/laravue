<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {name : The name of the model} {--modal : Use modal based CRUD}';

    protected $description = 'Create a full CRUD with Service, Repository, DTO, and Vue views';

    public function handle()
    {
        $name = $this->argument('name');
        $modelName = Str::studly($name);
        $modelVariable = Str::camel($modelName);
        $modelVariablePlural = Str::plural($modelVariable);
        $modelNamePlural = Str::plural($modelName);
        $tableName = Str::snake($modelNamePlural);

        $this->info("Generating CRUD for {$modelName}...");

        if (! Schema::hasTable($tableName)) {
            $this->error("Table '{$tableName}' does not exist. Please create the migration and run it first.");

            return 1;
        }

        $isModal = $this->option('modal') ?: $this->confirm('Do you want to use modal-based CRUD?', true);

        // Sidebar and Routes questions
        $devChoice = $this->choice(
            'Which developer\'s seeder should this be added to?',
            ['jayead', 'faysal', 'emon'],
            0
        );
        $sidebarTitle = $this->ask('Sidebar title', $modelNamePlural);
        $icon = $this->ask('Sidebar icon (Lucide icon name)', 'Circle');

        $columns = $this->getTableColumns($tableName);
        $relations = $this->getRelations($tableName);
        $mediaFields = $this->getMediaFields($columns);

        $data = [
            'modelName' => $modelName,
            'modelNamePlural' => $modelNamePlural,
            'modelVariable' => $modelVariable,
            'modelVariablePlural' => $modelVariablePlural,
            'tableName' => $tableName,
            'columns' => $columns,
            'relations' => $relations,
            'mediaFields' => $mediaFields,
            'isModal' => $isModal,
            'devName' => $devChoice,
            'sidebarTitle' => $sidebarTitle,
            'icon' => $icon,
        ];

        $this->generateFiles($data);
        $this->updateModel($data);
        $this->updateServiceProvider($data); // New method
        $this->generateRoutes($data);
        $this->updateSidebar($data);
        $this->updatePermissions($data);

        $this->info("CRUD for {$modelName} generated successfully!");
        $this->info("Routes created in routes/web/{$tableName}.php");
        $this->info("Sidebar entry added to database/seeders/Data/ModuleSeeder/{$devChoice}.php");
        $this->info("Permissions added to database/seeders/Data/RolesAndPermissions/{$devChoice}.php");
        $this->warn("Run 'php artisan db:seed' to update the sidebar and permissions in your database.");

        return 0;
    }

    protected function getTableColumns($table)
    {
        $columns = Schema::getColumns($table);
        $details = [];

        foreach ($columns as $column) {
            if (in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            $details[] = [
                'name' => $column['name'],
                'type' => $column['type_name'], // e.g. varchar, int, decimal
                'nullable' => $column['nullable'],
            ];
        }

        return $details;
    }

    protected function getRelations($table)
    {
        $foreignKeys = Schema::getForeignKeys($table);
        $relations = [];

        foreach ($foreignKeys as $fk) {
            $column = $fk['columns'][0];
            $relatedTable = $fk['foreign_table'];
            $relationName = Str::camel(Str::beforeLast($column, '_id'));
            $relatedModel = Str::studly(Str::singular($relatedTable));

            $relations[] = [
                'column' => $column,
                'name' => $relationName,
                'model' => $relatedModel,
            ];
        }

        // Fallback for naming convention if no formal FKs
        if (empty($relations)) {
            $columns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                if (Str::endsWith($column, '_id')) {
                    $relationName = Str::camel(Str::beforeLast($column, '_id'));
                    $relatedModel = Str::studly(Str::beforeLast($column, '_id'));
                    $relations[] = [
                        'column' => $column,
                        'name' => $relationName,
                        'model' => $relatedModel,
                    ];
                }
            }
        }

        return $relations;
    }

    protected function generateFiles($data)
    {
        $this->generateDTO($data);
        $this->generateRepositoryInterface($data);
        $this->generateRepository($data);
        $this->generateService($data);
        $this->generateRequests($data);
        $this->generateController($data);
        $this->generateVueFiles($data);
    }

    protected function getStub($type)
    {
        return File::get(base_path("stubs/crud/{$type}.stub"));
    }

    protected function generateDTO($data)
    {
        $stub = $this->getStub('dto');
        $properties = '';
        $fromModelAssignments = '';
        $toArrayAssignments = '';

        foreach ($data['columns'] as $col) {
            $phpType = $this->mapToPhpType($col['type']);
            $properties .= "        public {$phpType} \${$col['name']},\n";
            $fromModelAssignments .= "            {$col['name']}: \${$data['modelVariable']}->{$col['name']},\n";
            $toArrayAssignments .= "            '{$col['name']}' => \$this->{$col['name']},\n";
        }

        // Add relations to DTO
        foreach ($data['relations'] as $rel) {
            $properties .= "        public ?array \${$rel['name']} = null,\n";
            $fromModelAssignments .= "            {$rel['name']}: \${$data['modelVariable']}->{$rel['name']} ? \${$data['modelVariable']}->{$rel['name']}->toArray() : null,\n";
            $toArrayAssignments .= "            '{$rel['name']}' => \$this->{$rel['name']},\n";
        }

        $content = str_replace([
            '{{modelName}}',
            '{{modelVariable}}',
            '{{properties}}',
            '{{fromModelAssignments}}',
            '{{toArrayAssignments}}',
        ], [
            $data['modelName'],
            $data['modelVariable'],
            rtrim($properties),
            rtrim($fromModelAssignments),
            rtrim($toArrayAssignments),
        ], $stub);

        $path = app_path("DTOs/{$data['modelName']}/{$data['modelName']}Data.php");
        $this->writeFile($path, $content);
    }

    protected function generateRepositoryInterface($data)
    {
        $stub = $this->getStub('repository_interface');
        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
        ], $stub);

        $path = app_path("Repositories/{$data['modelNamePlural']}/{$data['modelName']}RepositoryInterface.php");
        $this->writeFile($path, $content);
    }

    protected function generateRepository($data)
    {
        $stub = $this->getStub('repository');
        $searchable = implode(', ', array_map(fn ($c) => "'{$c['name']}'", array_filter($data['columns'], fn ($c) => in_array($c['type'], ['string', 'text']))));
        $sortable = implode(', ', array_merge(["'id'"], array_map(fn ($c) => "'{$c['name']}'", $data['columns']), ["'created_at'"]));

        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
            '{{searchableFields}}',
            '{{sortableFields}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
            $searchable,
            $sortable,
        ], $stub);

        $path = app_path("Repositories/{$data['modelNamePlural']}/{$data['modelName']}Repository.php");
        $this->writeFile($path, $content);
    }

    protected function generateService($data)
    {
        $stub = $this->getStub('service');
        $selectColumns = implode(', ', array_merge(["'id'"], array_map(fn ($c) => "'{$c['name']}'", $data['columns']), ["'created_at'", "'updated_at'"]));
        $fillableFields = implode(', ', array_map(fn ($c) => "'{$c['name']}'", $data['columns']));

        $mediaUploadLogic = '';
        $mediaUpdateLogic = '';

        foreach ($data['mediaFields'] as $field) {
            $mediaUploadLogic .= "        if (request()->hasFile('{$field}')) {\n";
            $mediaUploadLogic .= "            \$item->addMediaFromRequest('{$field}')->toMediaCollection('{$field}');\n";
            $mediaUploadLogic .= "        }\n";

            $mediaUpdateLogic .= "        if (request()->hasFile('{$field}')) {\n";
            $mediaUpdateLogic .= "            \$updated->addMediaFromRequest('{$field}')->toMediaCollection('{$field}');\n";
            $mediaUpdateLogic .= "        }\n";
        }

        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
            '{{modelVariable}}',
            '{{tableName}}',
            '{{selectColumns}}',
            '{{fillableFields}}',
            '{{mediaUploadLogic}}',
            '{{mediaUpdateLogic}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
            $data['modelVariable'],
            $data['tableName'],
            $selectColumns,
            $fillableFields,
            trim($mediaUploadLogic),
            trim($mediaUpdateLogic),
        ], $stub);

        $path = app_path("Services/{$data['modelName']}Service.php");
        $this->writeFile($path, $content);
    }

    protected function getMediaFields($columns)
    {
        $mediaKeywords = ['image', 'logo', 'avatar', 'file', 'attachment', 'thumbnail'];
        $fields = [];
        foreach ($columns as $col) {
            foreach ($mediaKeywords as $keyword) {
                if (Str::contains($col['name'], $keyword)) {
                    $fields[] = $col['name'];
                    break;
                }
            }
        }

        return $fields;
    }

    protected function updateModel($data)
    {
        $path = app_path("Models/{$data['modelName']}.php");
        if (! File::exists($path)) {
            $this->error("Model file {$path} not found.");

            return;
        }

        $content = File::get($path);

        // Add imports
        $imports = [
            "use Illuminate\Database\Eloquent\Attributes\Fillable;",
            "use Modules\Cache\Traits\HasModelCache;",
        ];

        if (! empty($data['mediaFields'])) {
            $imports[] = "use Spatie\MediaLibrary\HasMedia;";
            $imports[] = "use Spatie\MediaLibrary\InteractsWithMedia;";
        }

        foreach ($imports as $import) {
            if (! Str::contains($content, $import)) {
                $content = preg_replace('/namespace App\\\Models;/', "namespace App\\Models;\n\n{$import}", $content);
            }
        }

        // Add implements HasMedia
        if (! empty($data['mediaFields']) && ! Str::contains($content, 'implements HasMedia')) {
            $content = preg_replace('/class '.$data['modelName'].' extends Model/', 'class '.$data['modelName'].' extends Model implements HasMedia', $content);
        }

        // Add Traits
        $traits = ['    use HasModelCache;'];
        if (! empty($data['mediaFields'])) {
            $traits[] = '    use InteractsWithMedia;';
        }

        foreach ($traits as $trait) {
            if (! Str::contains($content, $trait)) {
                $content = preg_replace('/{/', "{\n".$trait, $content);
            }
        }

        // Add Fillable attribute
        $fillableList = implode("', '", array_map(fn ($c) => $c['name'], $data['columns']));
        $fillableAttr = "#[Fillable(['{$fillableList}'])]\n";
        if (! Str::contains($content, '#[Fillable')) {
            $content = preg_replace('/class '.$data['modelName'].'/', "{$fillableAttr}class ".$data['modelName'], $content);
        }

        // Add Relations
        $relationsCode = '';
        foreach ($data['relations'] as $rel) {
            if (! Str::contains($content, "public function {$rel['name']}()")) {
                $relationsCode .= "\n    public function {$rel['name']}()\n";
                $relationsCode .= "    {\n";
                $relationsCode .= "        return \$this->belongsTo({$rel['model']}::class, '{$rel['column']}');\n";
                $relationsCode .= "    }\n";
            }
        }

        // Add registerMediaCollections
        if (! empty($data['mediaFields']) && ! Str::contains($content, 'registerMediaCollections')) {
            $relationsCode .= "\n    public function registerMediaCollections(): void\n";
            $relationsCode .= "    {\n";
            foreach ($data['mediaFields'] as $field) {
                $relationsCode .= "        \$this->addMediaCollection('{$field}')->singleFile();\n";
            }
            $relationsCode .= "    }\n";
        }

        if ($relationsCode) {
            $content = preg_replace('/}$/', $relationsCode."}\n", trim($content));
        }

        File::put($path, $content);
        $this->line("Updated Model: {$path}");
    }

    protected function generateRequests($data)
    {
        $stub = $this->getStub('request');
        $rules = '';
        foreach ($data['columns'] as $col) {
            $ruleSet = "['required'";

            // Media field specific rules
            if (in_array($col['name'], $data['mediaFields'])) {
                $isImage = Str::contains($col['name'], ['image', 'logo', 'avatar', 'thumbnail']);
                $ruleSet .= $isImage ? ", 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'" : ", 'file', 'max:10240'";
            } else {
                if ($col['type'] == 'string') {
                    $ruleSet .= ", 'string', 'max:255'";
                }
                if ($col['type'] == 'email' || Str::contains($col['name'], 'email')) {
                    $ruleSet .= ", 'email'";
                }
                if ($col['type'] == 'integer' || $col['type'] == 'bigint') {
                    $ruleSet .= ", 'integer'";
                }
                if ($col['type'] == 'decimal' || $col['type'] == 'float') {
                    $ruleSet .= ", 'numeric'";
                }
            }

            $ruleSet .= ']';
            $rules .= "            '{$col['name']}' => {$ruleSet},\n";
        }

        // Store Request
        $storeContent = str_replace([
            '{{modelNamePlural}}',
            '{{requestName}}',
            '{{rules}}',
        ], [
            $data['modelNamePlural'],
            "Store{$data['modelName']}Request",
            rtrim($rules),
        ], $stub);
        $this->writeFile(app_path("Http/Requests/{$data['modelNamePlural']}/Store{$data['modelName']}Request.php"), $storeContent);

        // Update Request
        $updateRules = str_replace("'required'", "'sometimes'", $rules);
        $updateContent = str_replace([
            '{{modelNamePlural}}',
            '{{requestName}}',
            '{{rules}}',
        ], [
            $data['modelNamePlural'],
            "Update{$data['modelName']}Request",
            rtrim($updateRules),
        ], $stub);
        $this->writeFile(app_path("Http/Requests/{$data['modelNamePlural']}/Update{$data['modelName']}Request.php"), $updateContent);
    }

    protected function generateController($data)
    {
        $stub = $this->getStub('controller');
        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
            '{{modelVariable}}',
            '{{modelVariablePlural}}',
            '{{tableName}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
            $data['modelVariable'],
            $data['modelVariablePlural'],
            $data['tableName'],
        ], $stub);

        $path = app_path("Http/Controllers/{$data['modelName']}Controller.php");
        $this->writeFile($path, $content);
    }

    protected function generateVueFiles($data)
    {
        $dir = resource_path("js/pages/{$data['modelNamePlural']}");
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if ($data['isModal']) {
            $this->generateVueIndexModal($data);
        } else {
            $this->generateVueIndexPage($data);
            $this->generateVueCreate($data);
            $this->generateVueEdit($data);
        }
    }

    protected function generateVueCreate($data)
    {
        $stub = $this->getStub('vue_create');

        $formFields = '';
        foreach ($data['columns'] as $col) {
            $formFields .= "    {$col['name']}: '',\n";
        }

        $vueFieldDefinitions = '';
        foreach ($data['columns'] as $col) {
            $label = Str::headline($col['name']);
            $type = 'text';
            if (in_array($col['name'], $data['mediaFields'])) {
                $type = 'file';
            } elseif ($col['type'] == 'decimal' || $col['type'] == 'integer') {
                $type = 'number';
            }
            if (Str::contains($col['name'], 'email')) {
                $type = 'email';
            }

            $vueFieldDefinitions .= "    { name: '{$col['name']}', label: '{$label}', type: '{$type}', required: true },\n";
        }

        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
            '{{modelVariablePlural}}',
            '{{formFields}}',
            '{{vueFieldDefinitions}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
            $data['modelVariablePlural'],
            rtrim($formFields),
            rtrim($vueFieldDefinitions),
        ], $stub);

        $this->writeFile(resource_path("js/pages/{$data['modelNamePlural']}/Create.vue"), $content);
    }

    protected function generateVueEdit($data)
    {
        $stub = $this->getStub('vue_edit');

        $editFormFields = '';
        foreach ($data['columns'] as $col) {
            $editFormFields .= "    {$col['name']}: props.{$data['modelVariable']}.{$col['name']},\n";
        }

        $vueFieldDefinitions = '';
        foreach ($data['columns'] as $col) {
            $label = Str::headline($col['name']);
            $type = 'text';
            if (in_array($col['name'], $data['mediaFields'])) {
                $type = 'file';
            } elseif ($col['type'] == 'decimal' || $col['type'] == 'integer') {
                $type = 'number';
            }
            if (Str::contains($col['name'], 'email')) {
                $type = 'email';
            }

            $vueFieldDefinitions .= "    { name: '{$col['name']}', label: '{$label}', type: '{$type}', required: true },\n";
        }

        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
            '{{modelVariable}}',
            '{{modelVariablePlural}}',
            '{{editFormFields}}',
            '{{vueFieldDefinitions}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
            $data['modelVariable'],
            $data['modelVariablePlural'],
            rtrim($editFormFields),
            rtrim($vueFieldDefinitions),
        ], $stub);

        $this->writeFile(resource_path("js/pages/{$data['modelNamePlural']}/Edit.vue"), $content);
    }

    protected function generateVueIndexModal($data)
    {
        $stub = $this->getStub('vue_index_modal');
        $vueColumns = '';
        foreach ($data['columns'] as $col) {
            $label = Str::headline($col['name']);
            $vueColumns .= "    column('{$col['name']}', '{$label}'),\n";
        }

        $formFields = '';
        $editFormAssignments = '';
        foreach ($data['columns'] as $col) {
            $formFields .= "    {$col['name']}: '',\n";
            $editFormAssignments .= "    editForm.{$col['name']} = item.{$col['name']};\n";
        }

        $vueFieldDefinitions = '';
        foreach ($data['columns'] as $col) {
            $label = Str::headline($col['name']);
            $type = 'text';
            if (in_array($col['name'], $data['mediaFields'])) {
                $type = 'file';
            } elseif ($col['type'] == 'decimal' || $col['type'] == 'integer') {
                $type = 'number';
            }
            if (Str::contains($col['name'], 'email')) {
                $type = 'email';
            }

            $vueFieldDefinitions .= "    { name: '{$col['name']}', label: '{$label}', type: '{$type}', required: true },\n";
        }

        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
            '{{modelVariable}}',
            '{{modelVariablePlural}}',
            '{{vueColumns}}',
            '{{formFields}}',
            '{{vueFieldDefinitions}}',
            '{{editFormAssignments}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
            $data['modelVariable'],
            $data['modelVariablePlural'],
            rtrim($vueColumns),
            rtrim($formFields),
            rtrim($vueFieldDefinitions),
            rtrim($editFormAssignments),
        ], $stub);

        $this->writeFile(resource_path("js/pages/{$data['modelNamePlural']}/Index.vue"), $content);
    }

    protected function generateVueIndexPage($data)
    {
        $stub = $this->getStub('vue_index_page');
        $vueColumns = '';
        foreach ($data['columns'] as $col) {
            $label = Str::headline($col['name']);
            $vueColumns .= "    column('{$col['name']}', '{$label}'),\n";
        }

        $content = str_replace([
            '{{modelName}}',
            '{{modelNamePlural}}',
            '{{modelVariable}}',
            '{{modelVariablePlural}}',
            '{{vueColumns}}',
        ], [
            $data['modelName'],
            $data['modelNamePlural'],
            $data['modelVariable'],
            $data['modelVariablePlural'],
            rtrim($vueColumns),
        ], $stub);

        $this->writeFile(resource_path("js/pages/{$data['modelNamePlural']}/Index.vue"), $content);
    }

    protected function generateRoutes($data)
    {
        $stub = $this->getStub('routes');
        $content = str_replace([
            '{{modelName}}',
            '{{tableName}}',
        ], [
            $data['modelName'],
            $data['tableName'],
        ], $stub);

        $path = base_path("routes/web/{$data['tableName']}.php");
        $this->writeFile($path, $content);
    }

    protected function updateSidebar($data)
    {
        $path = database_path("seeders/Data/ModuleSeeder/{$data['devName']}.php");
        if (! File::exists($path)) {
            $this->error("Seeder file {$path} not found.");

            return;
        }

        $content = File::get($path);

        // Find all IDs to get the next one
        preg_match_all('/\'id\' => (\d+),/', $content, $matches);
        $ids = ! empty($matches[1]) ? array_map('intval', $matches[1]) : [];

        // Find all sort_orders to get the next one
        preg_match_all('/\'sort_order\' => (\d+),/', $content, $matches);
        $sortOrders = ! empty($matches[1]) ? array_map('intval', $matches[1]) : [];

        $nextId = empty($ids) ? 100 : max($ids) + 1;

        // Ensure ID stays in range if file is empty
        if ($data['devName'] === 'emon' && $nextId < 100) {
            $nextId = 100;
        }
        if ($data['devName'] === 'faysal' && $nextId < 200) {
            $nextId = 200;
        }
        if ($data['devName'] === 'jayead' && $nextId < 300) {
            $nextId = 300;
        }

        $nextSortOrder = empty($sortOrders) ? 10 : max($sortOrders) + 10;

        $newEntry = "    [
        'id' => {$nextId},
        'title' => '{$data['sidebarTitle']}',
        'route_name' => '{$data['tableName']}.index',
        'icon' => '{$data['icon']}',
        'permission' => '{$data['tableName']}.view',
        'sort_order' => {$nextSortOrder},
    ],
];";

        $updatedContent = preg_replace('/\];\s*$/', $newEntry, $content);

        if ($updatedContent !== $content) {
            File::put($path, $updatedContent);
            $this->line("Updated sidebar seeder: {$path}");
        } else {
            $this->error('Could not update sidebar seeder. Please add the entry manually.');
        }
    }

    protected function updatePermissions($data)
    {
        $path = database_path("seeders/Data/RolesAndPermissions/{$data['devName']}.php");
        if (! File::exists($path)) {
            $this->error("Permissions seeder file {$path} not found.");

            return;
        }

        $content = File::get($path);

        $newPermissions = "
    '{$data['tableName']}.view',
    '{$data['tableName']}.create',
    '{$data['tableName']}.edit',
    '{$data['tableName']}.delete',
    '{$data['tableName']}.bulk-delete',
];";

        $updatedContent = preg_replace('/\];\s*$/', $newPermissions, $content);

        if ($updatedContent !== $content) {
            File::put($path, $updatedContent);
            $this->line("Updated permissions seeder: {$path}");
        } else {
            $this->error('Could not update permissions seeder.');
        }
    }

    protected function updateServiceProvider($data)
    {
        $path = app_path('Providers/AppServiceProvider.php');
        if (! File::exists($path)) {
            $this->error("ServiceProvider file {$path} not found.");

            return;
        }

        $content = File::get($path);

        $interfaceImport = "use App\\Repositories\\{$data['modelNamePlural']}\\{$data['modelName']}RepositoryInterface;";
        $repositoryImport = "use App\\Repositories\\{$data['modelNamePlural']}\\{$data['modelName']}Repository;";
        $binding = "\$this->app->singleton({$data['modelName']}RepositoryInterface::class, {$data['modelName']}Repository::class);";

        // Add imports
        if (! Str::contains($content, $interfaceImport)) {
            $content = preg_replace('/namespace App\\\Providers;/', "namespace App\\Providers;\n\n{$interfaceImport}\n{$repositoryImport}", $content);
        }

        // Add binding in register method
        if (! Str::contains($content, $binding)) {
            $content = preg_replace('/public function register\(\): void\n\s*{/', "public function register(): void\n    {\n        {$binding}", $content);
        }

        File::put($path, $content);
        $this->line("Updated ServiceProvider: {$path}");
    }

    protected function writeFile($path, $content)
    {
        $directory = dirname($path);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($path)) {
            if (! $this->confirm("File {$path} already exists. Overwrite?", false)) {
                return;
            }
        }

        File::put($path, $content);
        $this->line("Created: {$path}");
    }

    protected function mapToPhpType($type)
    {
        return match ($type) {
            'integer', 'bigint' => 'int',
            'boolean' => 'bool',
            'decimal', 'float' => 'string', // Usually cast to string in DTO for precision
            default => 'string',
        };
    }
}
