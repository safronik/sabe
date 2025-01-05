<?php

namespace Safronik\Models\EntityManager;

use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\SchemaProviders\MetaObjects\MetaEntity;
use Safronik\Models\Entities\Value;

class ObjectsTree
{
    private array $tree = [];

    public function __construct(
        protected string $domains_directory = 'App/Models/Entities',
        protected string $root_namespace = 'Models\Entities'
    ){}

    public function buildTree(): void
    {
        $this->tree ?: $this->setTreeRoot($this->domains_directory, $this->root_namespace);
        $this->buildTreeRecursive( $this->root_namespace );
    }

    protected function buildTreeRecursive( string $namespace ): void
    {
        $tree[ $namespace ] = [];
        $files = scandir( $this->getNamespacePath( $namespace ) );
        foreach( $files as $file )
        {
            if( $file === '.' || $file === '..' )
            {
                continue;
            }
            $path = $this->getNamespacePath( $namespace . '\\' . $file );
            if( is_dir( $path ) )
            {
                $this->getTreeRecursive( $namespace . '\\' . $file, $tree[ $namespace ] );
            }
            else
            {
                $tree[ $namespace ][] = $file;
            }
        }
    }

    private function setTreeRoot( string $domains_directory, string $entity_root_namespace ): void
    {
        foreach( new \RecursiveDirectoryIterator($domains_directory) as $file ){

            if( $file->isFile() && $file->getExtension() === 'php' ){

                $entity_class = $entity_root_namespace . $file->getPath() . '/' . $file->getBasename( '.php' );
                $entity_class = str_replace(
                    [ $domains_directory, '/' ],
                    [ '', '\\' ],
                    $entity_class
                );

                if( is_a( $entity_class, Entity::class ) ){
                    $this->tree[] = new MetaEntity( $entity_class, $entity_root_namespace );
                }
            }
        }
    }

    protected function getNamespacePath( string $namespace ): string
    {
        return str_replace( '\\', '/', $namespace );
    }
}