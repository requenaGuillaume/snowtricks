<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $groups = [
            [
                // mute // sad // indy // stalefish // tail grab // nose grab // japan ou japan air // seat belt // truck driver
                'title' => 'grab',
                'description' => 'A grab consists in catching the board with the hand during the jump. The English verb to grab means "to catch."'
            ],
            [
                // 180 // 360 // 540 // 720 // 900 // 1080
                'title' => 'rotation',
                'description' => '
                        The word "rotation" designates only horizontal rotations; vertical rotations are flips.
                        The principle is to perform a horizontal rotation during the jump, then to land in a switch or normal position.
                        The nomenclature is based on the number of degrees of rotation performed
                    '
            ],
            [
                // Japan air //  rocket air // Backside Air // Method Air
                'title' => 'old school',
                'description' => '
                        The term old school designates a style of freestyle characterized by a set of tricks and a way of performing 
                        tricks that have gone out of fashion, reminiscent of the freestyle of the 1980s - early 1990s   
                    '
            ]
        ];

        foreach($groups as $groupInfos){
            $group = new Group();
            $group->setTitle($groupInfos['title'])
                ->setDescription($groupInfos['description']);
            
            $manager->persist($group);
        }

        // Users & Comments
        // for($i = 0; $i < 10; $i++){

        // }

        // 10 Tricks

        $manager->flush();
    }
}
