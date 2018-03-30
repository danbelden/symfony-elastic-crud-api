<?php

namespace Tests\AppBundle\Form;

use AppBundle\Form\Create as CreateForm;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;

class CreateTest extends KernelTestCase
{
    /**
     * @var Form
     */
    private $form;

    public function setup()
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();
        $formFactory = $container->get('form.factory');

        $this->form = $formFactory->create(
            CreateForm::class,
            null,
            [
                'csrf_protection' => false
            ]
        );
    }

    public function testFormFields()
    {
        $this->assertSame(1, $this->form->count());

        $hasNameField = $this->form->has('name');
        $this->assertTrue($hasNameField);
    }

    public function testFormWithValidData()
    {
        $submittedData = ['name' => 'test'];
        $this->form->submit($submittedData);

        $isValid = $this->form->isValid();
        $this->assertTrue($isValid);

        $formData = $this->form->getData();
        $this->assertInternalType('array', $formData);
        $this->assertArrayHasKey('name', $formData);
        $this->assertSame($submittedData['name'], $formData['name']);
    }

    public function testFormWithInvalidData()
    {
        $submittedData = ['name' => ''];
        $this->form->submit($submittedData);

        $isValid = $this->form->isValid();
        $this->assertFalse($isValid);
    }
}
