<?php

declare(strict_types=1);

namespace t3n\GraphQL\Service;

use Neos\Flow\Annotations as Flow;
use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ResolveInfo;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;

class DefaultFieldResolver
{
    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;
    
    /**
     * @param mixed $source
     * @param mixed[] $args
     * @param mixed $context
     *
     * @return mixed|null
     */
    public static function resolve($source, array $args, $context, ResolveInfo $info)
    {
        $resolvedProperty = Executor::defaultFieldResolver($source, $args, $context, $info);
        if ($resolvedProperty !== null) {
            return $resolvedProperty;
        }

        $fieldName = $info->fieldName;
        if (is_object($source) && ObjectAccess::isPropertyGettable($source, $fieldName)) {
            $resolvedProperty = ObjectAccess::getProperty($source, $fieldName);
        } else if( $fieldName === '__identity' ) {
            $persistenceManager = new PersistenceManager();
            $resolvedProperty = $persistenceManager->getIdentifierByObject($source);
        }

        if (is_callable($resolvedProperty)) {
            return $resolvedProperty($source, $args, $context, $info);
        }

        return $resolvedProperty;
    }
}
