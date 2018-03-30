<?php

namespace Tests\AppBundle\Form;

use AppBundle\Form\Update as UpdateForm;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;

class UpdateTest extends KernelTestCase
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
            UpdateForm::class,
            [],
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
        $preData = $this->form->getData();
        $this->assertInternalType('array', $preData);
        $this->assertSame([], $preData);

        $submittedData = ['name' => 'test2'];
        $this->form->submit($submittedData);

        $isValid = $this->form->isValid();
        $this->assertTrue($isValid);

        $postData = $this->form->getData();
        $this->assertInternalType('array', $postData);
        $this->assertArrayHasKey('name', $postData);
        $this->assertSame($submittedData['name'], $postData['name']);
    }

    public function testFormWithInvalidData()
    {
        $submittedData = ['name' => ''];
        $this->form->submit($submittedData);

        $isValid = $this->form->isValid();
        $this->assertFalse($isValid);
    }
}
