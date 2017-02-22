<?php
namespace Application\Hydrator;

use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Exception;

class PropelHydrator extends AbstractHydrator
{
    protected $collectionMetadata;

    /**
     * @param object $object
     * @return array
     */
    public function extract($object)
    {
        $self = $this;

        if (method_exists($object, 'toArrayWithRelations')) {
            $data = $object->toArrayWithRelations();
        } else {
            $data = $object->toArray();
        }

       	array_walk($data, function(&$value, $name) use ($self) {
            $value = $self->extractValue($name, $value);
        });

        return $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        $self = $this;
        array_walk_recursive($data, function(&$value, $name) use ($self) {
            $value = $self->hydrateValue($name, $value);
        });

        if (method_exists($object,'exchangeArray') && is_callable(array($object, 'exchangeArray'))) {
            $object->exchangeArray($data);
        } else {
            array_walk_recursive($data, function(&$value, $name) use ($object) {
                if(method_exists($object, 'set'.$name))
                    $object->{'set'.$name}($value);
            });
        }

        return $object;
    }

}