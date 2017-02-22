<?php
namespace Application\Form;

use Zend\Form\Form;
use Application\Hydrator\PropelHydrator;
use Zend\InputFilter\InputFilterAwareInterface;
use Application\Hydrator\Strategy\DateTimeStrategy;

abstract class AbstractBaseForm extends Form
{
	/**
	 * Does this form require an extract/hydrate strategy to handle datetime types
	 * @var bool
	 */
	protected $requiresDateTimeStrategy = false;

	/**
	 * If a select element only contains a single option select it by default
	 * @var bool
	 */
	protected $preSelectSingleValues = true;


	public function __construct($name)
	{
		parent::__construct($name);

		$this->setHydrator(new PropelHydrator());

		$this->add(array(
			'name' => $name.'csrf',
			'type' => 'csrf',
			'options' => array(
				'csrf_options' => array(
					'timeout' => 1800,
				),
			),
		));
	}

	/**
	 * @param bool $preSelect
	 * @return $this
	 */
	public function setPreSelectSingleValue($preSelect = true)
	{
		$this->preSelectSingleValues = $preSelect;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getPreSelectSingleValue()
	{
		return $this->preSelectSingleValues;
	}

	/**
	 * @param object $object
	 * @param int $flags
	 * @return $this
	 */
	public function bind($object, $flags = \Zend\Form\FormInterface::VALUES_NORMALIZED)
	{
		parent::bind($object, $flags);
		$this->setRequiredFields();
		$this->addStrategies();

		if ($this->preSelectSingleValues)
		{
			$this->preSelectSingleValues();
		}

		return $this;
	}

	/**
	 * @return void
	 */
	public function preSelectSingleValues()
	{
		foreach($this->getElements() as $element)
		{
			if ($element instanceOf \Zend\Form\Element\Select)
			{
				$options = $element->getOptions();
				if (isset($options['value_options']) && count($options['value_options']) == 1)
				{
					$element->setValue(array_shift($options['value_options']));
				}
			}
		}
	}

	/**
	 * generate form elements for this form from the associated bound objects table map class
	 *
	 * @param string $tableMapClass a TableMap class in Db/Map representing the object to be bound
	 * @param array $excludeColumns an array of column names to exclude from the form
	 * @param array $columnOptions an array of other (options) options to include for each element
	 *
	 * @return AbstractBaseForm
	 */
	public function generateFromTableMap($tableMapClass, array $excludeColumns = ['id', 'created_at', 'updated_at'], array $columnOptions = [])
	{
		// check the table map class exists
		$qualifiedClass = '\Map\\' . $tableMapClass;
		$table = $qualifiedClass::getTableMap();
		$columns = $table->getColumns();

		foreach($columns as $column)
		{
			$name = $column->getName();

			if (in_array($name, $excludeColumns))
				continue;

			$mapType = strtoupper($column->getType());
			$type = $this->getFormTypeFromMapType($mapType);
			$label = $this->getFormLabelFromMapName($name);

			$elementConfig = [
				'name' => $name,
				'type' => $type,
				'options' => [
					'add-on-append' => ($mapType == 'TIMESTAMP' ? '<i class="fa fa-calendar datepicker-append"></i>' : ''),
					'label' => $label,
				],
				'attributes' => [
					'class' => ($mapType == 'TIMESTAMP' ? 'datepicker tablemap-form-element' : 'tablemap-form-element'),
				],
			];

			if ($type == 'select')
			{
				$elementConfig['options']['empty_option'] = 'PLEASE_SELECT';
				if ($mapType == 'BOOLEAN')
				{
					$elementConfig['options']['value_options'] = [0 => 'NO', 1 => 'YES'];
				}
			}

			if (isset($columnOptions[$name]))
			{
				if (isset($columnOptions[$name]['type']))
				{
					$elementConfig['type'] = $columnOptions[$name]['type'];
				}

				if (isset($columnOptions[$name]['add-on-append']))
				{
					$elementConfig['options']['add-on-append'] = $columnOptions[$name]['add-on-append'];
				}

				if (isset($columnOptions[$name]['add-on-preppend']))
				{
					$elementConfig['options']['add-on-preppend'] = $columnOptions[$name]['add-on-preppend'];
				}

				if (isset($columnOptions[$name]['class']))
				{
					$elementConfig['attributes']['class'] = $columnOptions[$name]['class'];
				}

				if (isset($columnOptions[$name]['label']))
				{
					$elementConfig['options']['label'] = $columnOptions[$name]['label'];
				}

				if (isset($columnOptions[$name]['empty_option']))
				{
					$elementConfig['options']['empty_option'] = $columnOptions[$name]['empty_option'];
				}

				if (isset($columnOptions[$name]['value_options']))
				{
					$elementConfig['options']['value_options'] = $columnOptions[$name]['value_options'];
				}
			}

			$this->add($elementConfig);
		}

		$this->add([
			'name' => 'save',
			'type' => 'Submit',
			'attributes' => [
				'value' => 'SAVE_DETAILS',
				'class' => 'btn btn-primary',
				'id' => 'submit-plant-form',
			]
		]);

		return $this;
	}



	/**
	 * return a form element label translation string based on a propel column name
	 * @param $mapName
	 *
	 * @return string
	 */
	private function getFormLabelFromMapName($mapName)
	{
		// split the map name on capitals
		$pieces = preg_split('/(?=[A-Z])/', lcfirst($mapName));
		return strtoupper(implode('_', $pieces));
	}

	/**
	 * return a form element type based on a propel column type
	 * @param $mapType
	 *
	 * @return string
	 */
	private function getFormTypeFromMapType($mapType)
	{
		$mapType = strtoupper($mapType);
		switch($mapType)
		{
			case 'BOOLEAN' :
				return 'select';
				break;
			case 'LONGVARCHAR' :
				return 'textarea';
				break;
			case 'TIMESTAMP' :
				return 'date';
				break;
			case 'INTEGER' :
			case 'VARCHAR' :
			default:
				return 'text';
				break;
		}
	}

	/**
	 * assign classes to required form elements
	 *
	 * @return void
	 */
	public function setRequiredFields()
	{
		$object = $this->getObject();
		if (is_null($object))
			return;

		if ($object instanceOf InputFilterAwareInterface)
		{
			$inputs = $object->getInputFilter()->getInputs();
			foreach($inputs as $fieldName => $input)
			{
				try {
					if (!is_null($this->get($fieldName)) && $input->isRequired())
					{
						$this->get($fieldName)->setAttribute('class', $this->get($fieldName)->getAttribute('class') . ' required ');
					}
				} catch (\Exception $e) {}
			}
		}
		return;
	}

	/**
	 * append hydration strategies based on elements contained in this form
	 */
	private function addStrategies()
	{
		foreach($this->getElements() as $element)
		{
			$type = $element->getAttribute('type');
			switch ($type)
			{
				case 'date' :
					$this->getHydrator()->addStrategy($element->getName(), new DateTimeStrategy('Y-m-d H:i'));
					break;
			}
		}

		$this->setPreferFormInputFilter(true);

		return $this;
	}


}