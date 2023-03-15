<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Group;
use App\Entity\Trick;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    
    private const GROUPS_INFOS = [
        [
            'title' => 'grab',
            'description' => 'A grab consists in catching the board with the hand during the jump. The English verb to grab means "to catch."'
        ],
        [
            'title' => 'rotation',
            'description' => '
                    The word "rotation" designates only horizontal rotations; vertical rotations are flips.
                    The principle is to perform a horizontal rotation during the jump, then to land in a switch or normal position.
                    The nomenclature is based on the number of degrees of rotation performed
                '
        ],
        [
            'title' => 'old school',
            'description' => '
                    The term old school designates a style of freestyle characterized by a set of tricks and a way of performing 
                    tricks that have gone out of fashion, reminiscent of the freestyle of the 1980s - early 1990s   
                '
        ]
    ];

    private const TRICKS_INFOS = [
        // Grab
        [
            'title' => 'Mute',
            'description' => '
                    In this trick, when a player is in the air, he has to bend down on the board and grab the toe edge of the 
                    board between his two legs. He also has to grab the the middle edge of the board using his front hand at that moment.
                ',
            'photos' => ['19.jpg', '20.jpg', '21.jpg']
        ],
        [
            'title' => 'sad',
            'description' => '
                    Quite similar to a nosebone but you grab with the front hand and on the heelside while the nose of the board points 
                    towards the ground.
                ',
            'photos' => ['24.jpg']
        ],
        [
            'title' => 'indy',
            'description' => '
                    An Indy grab, also known as an Indy air, is an aerial skateboarding, snowboarding and kitesurfing trick during which 
                    the rider grabs their back hand on the middle of their board, between their feet, on the side of the board where their 
                    toes are pointing, while turning backside.
                ',
            'photos' => ['1.png', '2.jpg', '3.jpg']
        ],
        // Rotation
        [
            'title' => '180',
            'description' => '
                    A 180 is essentially spinning your snowboard, in the air, 180 degrees. You will start out facing one way down the 
                    mountain and end up facing the other way. Before learning how to do a 180 you should already be comfortable with 
                    ollies and with riding switch.
                ',
            'photos' => ['12.png', '13.jpg']
        ],
        [
            'title' => '360',
            'description' => '
                    A frontside 360 is when you leave the slope and rotate in the air 360 degrees before hitting the ground again.
                ',
            'photos' => ['4.jpg', '5.jpg', '6.jpg']
        ],
        [
            'title' => '1080',
            'description' => '
                    A 1080 consists of three full rotations in the air.
                ',
            'photos' => ['7.jpg', '8.png']
        ],
        // Old school
        [
            'title' => 'japan air',
            'description' => '
                    “Japan Air” is the name of a trick in which the airborne athlete takes his front hand, reaches down over his front 
                    leg to grab the edge of his snowboard nearest to his toes—the “toe edge”—and then pulls the board behind him.
                ',
            'photos' => ['14.jpg', '15.png']
        ],
        [
            'title' => 'rocket air',
            'description' => '
                    Achieved when rider takes both hands and grabs the nose of the snowboard. Rusty Trombone - Combination of a 
                    roast beef and nose grab done at the same time.
                ',
            'photos' => ['22.jpg', '23.png']
        ],
        [
            'title' => 'Backside Air',
            'description' => '
                    A trick performed on the backside wall of the halfpipe where the athlete grabs the heel edge of the board with 
                    the front hand. Backside Handplant: A maneuver where the rider places either both hands or the rear hand on 
                    the lip of the halfpipe and rotates 180 degrees in the backside direction.    
                ',
            'photos' => ['9.jpg', '10.png', '11.png']
        ],
        [
            'title' => 'Method Air',
            'description' => '
                    A trick where the boarder grabs the heel edge of the board with their front hand, between their feet, and then 
                    pulls the board towards their back, while arching their back and bending knees.
                ',
            'photos' => ['16.png', '17.png', '18.jpg']
        ],
    ];


    public function __construct(private SluggerInterface $slugger)
    {        
    }

    public function load(ObjectManager $manager): void
    {
        $groups = [];

        foreach(self::GROUPS_INFOS as $groupInfos){
            $group = new Group();
            $group->setTitle($groupInfos['title'])
                ->setDescription($groupInfos['description']);
            
            $manager->persist($group);

            $groups[] = $group;
        }

        $faker = Factory::create();

        // Users
        $users = [];
        for($i = 0; $i < 9; $i++){
            $user = new User();
            $user->setUsername($faker->name())
                ->setEmail($faker->email())
                ->setPassword(password_hash('password', PASSWORD_DEFAULT))
                ->setIsVerified(true);

            $manager->persist($user);

            $users[] = $user;
        }

        // 10 Tricks
        for($t = 0; $t < 9; $t++){
            $trick = new Trick();

            $groupIndex = 0;

            if($t > 2 && $t <= 5){
                $groupIndex = 1;
            }

            if($t > 5){
                $groupIndex = 2;
            }

            $title = self::TRICKS_INFOS[$t]['title'];

            // TODO : setComments - addComments
            $trick->setCategory($groups[$groupIndex])
                ->setAuthor($users[$t])
                ->setTitle($title)
                ->setSlug(strtolower($this->slugger->slug($title)))
                ->setDescription(self::TRICKS_INFOS[$t]['description']);
        }

        $manager->flush();
    }
}