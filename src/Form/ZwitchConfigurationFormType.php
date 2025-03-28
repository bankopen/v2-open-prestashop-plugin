<?php
/**
 * @author MY-Dev |Mohamed Youssef <mydev@my-dev.pro>
 */
namespace PrestaShop\Module\Zwitch\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ZwitchConfigurationFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('access_key', TextType::class, [
                'label' => $this->trans('Access Key', 'Modules.Zwitch.Admin'),
                'help' => $this->trans('Maximum 32 characters', 'Modules.Zwitch.Admin'),
            ])
            ->add('secret_key', TextType::class, [
                'label' => $this->trans('Secret Key', 'Modules.Zwitch.Admin'),
                'help' => $this->trans('Maximum 32 characters', 'Modules.Zwitch.Admin'),
            ])
            ->add('sandbox_mode', ChoiceType::class, [
                'label' => $this->trans('Sandbox', 'Modules.Zwitch.Admin'),
                'choices' => [
                    'Enable' => '1',
                    'Disable' => '0',
                ],
                'placeholder' => $this->trans('Choose an environment', 'Modules.Zwitch.Admin'),
                'required' => true,
            ]);
    }
}