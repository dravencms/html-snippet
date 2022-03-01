<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\HtmlSnippet\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Dravencms\Model\Admin\Entities\Menu;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class AdminMenuFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $menu = $manager->getRepository(Menu::class);

        $adminMenu = new Menu('HtmlSnippet', ':Admin:HtmlSnippet:HtmlSnippet', 'fa-code', $this->getReference('user-acl-operation-htmlSnippet-edit'));

        if ($parent = $menu->findOneBy(['name' => 'Site items']))
        {
            $menu->persistAsLastChildOf($adminMenu, $parent);
        }
        else
        {
            $manager->persist($adminMenu);
        }

        $manager->flush();
    }
    /**
     * Get the order of this fixture
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return ['Dravencms\Model\HtmlSnippet\Fixtures\AclOperationFixtures', 'Dravencms\Model\Structure\Fixtures\AdminMenuFixtures'];
    }
}