<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ruvents\AdminBundle\Config;
use Ruvents\AdminBundle\Controller;
use Ruvents\AdminBundle\Controller\ArgumentValueResolver;
use Ruvents\AdminBundle\ListField\TypeContextProcessor\AssociationTypeContextProcessor;
use Ruvents\AdminBundle\ListField\TypeGuesser\DoctrineTypeGuesser;
use Ruvents\AdminBundle\ListField\TypeGuesser\UploadTypeGuesser;
use Ruvents\AdminBundle\Menu\MenuResolver;
use Ruvents\AdminBundle\Twig;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

return function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
        ->private()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(Config\ConfigManager::class)
        ->args([
            '$cache' => ref('ruvents_admin.config.cache'),
            '$passes' => tagged('ruvents_admin.config_pass'),
        ]);

    $services
        ->set(Config\Pass\BuildEntitiesPass::class)
        ->tag('ruvents_admin.config_pass', ['priority' => 1024]);

    $services
        ->set(Config\Pass\BuildMenuPass::class)
        ->tag('ruvents_admin.config_pass', ['priority' => 1000]);

    $services
        ->set(Config\Pass\FormFieldsPass::class)
        ->tag('ruvents_admin.config_pass', ['priority' => 512]);

    $services
        ->set(Config\Pass\ResolveFormThemePass::class)
        ->tag('ruvents_admin.config_pass', ['priority' => 256]);

    $services
        ->set('ruvents_admin.config.cache', FilesystemCache::class)
        ->args(['ruvents_admin', 0, '%kernel.cache_dir%']);

    $services
        ->set(Config\Model\Config::class)
        ->factory([ref(Config\ConfigManager::class), 'getConfig']);

    $services->set(Controller\IndexController::class)->public();
    $services->set(Controller\ListController::class)->public();
    $services->set(Controller\CreateController::class)->public();
    $services->set(Controller\EditController::class)->public();

    $services->set(Twig\ConfigExtension::class);
    $services->set(Twig\ListExtension::class)
        ->args([
            '$guessers' => tagged('ruvents_admin.list_field_type_guesser'),
            '$processors' => tagged('ruvents_admin.list_field_type_value_guesser'),
        ]);
    $services->set(Twig\MenuExtension::class);

    $services->set(ArgumentValueResolver\EntityConfigResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 150]);

    $services->set('ruvents_admin.menu.language', ExpressionLanguage::class);

    $services->set(MenuResolver::class)
        ->args([
            '$language' => ref('ruvents_admin.menu.language'),
        ]);

    $services->set(DoctrineTypeGuesser::class)
        ->tag('ruvents_admin.list_field_type_guesser', ['priority' => 100]);

    $services->set(UploadTypeGuesser::class)
        ->tag('ruvents_admin.list_field_type_guesser', ['priority' => 110]);

    $services->set(AssociationTypeContextProcessor::class)
        ->tag('ruvents_admin.list_field_type_context_processor');
};
