<?php
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class LoginForm extends Form implements InputFilterProviderInterface
{
	
	public function __construct()
	{
		parent::__construct('login');
		
		$this->add([
			'type' => 'Zend\Form\Element\Csrf',
			'name' => 'logincsrf',
			'attributes' => [
				'type' => 'hidden'
			],
			'options' => [
				'csrf_options' => [
					'timeout' => 900
				]
			]
		]);
		
		$this->add([
			'name' => 'email',
			'attributes' => [
				'type' => 'email',
			],
			'options' => [
				'label' => 'EMAIL_ADDRESS_OR_USERNAME',
			],
			'attributes' => [
				'placeholder' => 'Enter your university username or email address',
			]
		]);
		
		$this->add([
			'name' => 'password',
			'attributes' => [
				'type' => 'password',
			],
			'options' => [
				'label' => 'PASSWORD',
			]
		]);
		
		$this->add([
			'name' => 'submit',
			'options' => [
				'label' => '',
			],
			'attributes' => [
				'type' => 'submit',
				'value' => 'LOGIN',
				'class' => 'btn-primary'
			],
		]);
		
	}
	
	public function getInputFilterSpecification()
	{
		return [
			'email' => [
				'required' => true,
			],
			'password' => [
				'required' => true
			],
		];
	}
	
}