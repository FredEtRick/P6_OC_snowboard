<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Video;
use App\Entity\Figure;
use App\Entity\Groupe;
use App\Entity\Commentaire;
use App\Entity\Utilisateur;
use Faker\Provider\Youtube;
use App\Entity\Illustration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');

        $moderateurExiste = false;
        $adminExiste = false;

        $genres = ['homme', 'femme'];

        $utilisateurs = array();

        for ($i=0 ; $i<8 ; $i++){
            $utilisateur = new Utilisateur();

            $urlAvatar = "https://randomuser.me/api/portraits/";
            $genre = $faker->randomElement($genres);
            $idAvatar = $faker->numberBetween(1, 99);

            $avatar = $urlAvatar . $genre . "/" . $idAvatar . ".jpg";

            $utilisateur->setLogin($faker->word . mt_rand(1000, 9999))
                        ->setMotDePasse(str_shuffle("azertyuiopqsdFGHJKLMWXCVBN1234567890"))
                        ->setMail($faker->email);
            
            if ($faker->numberBetween(0, 3) != 0)
                $utilisateur->setAvatar($avatar);
            
            if (! $moderateurExiste)
            {
                $utilisateur->setRole("moderateur");
                $moderateurExiste = true;
            }
            elseif (! $adminExiste)
            {
                $utilisateur->setRole("administrateur");
                $adminExiste = true;
            }
            else
                $utilisateur->setRole("utilisateur");

            $manager->persist($utilisateur);
            array_push($utilisateurs, $utilisateur);
        }

        $groupes = array();

        for ($j=0 ; $j<4 ; $j++)
        {
            $groupe = new Groupe();

            $nom = $faker->word;

            $groupe->setNom($nom);
            
            $manager->persist($groupe);
            array_push($groupes, $groupe);
        }

        $figures = array();

        for ($k=0 ; $k<20 ; $k++)
        {
            $figure = new Figure();

            $nom = $faker->sentence(3);

            $figure->setNom($nom)
                   ->setDescription(implode(" ", $faker->sentences()))
                   ->setEditeur($utilisateurs[array_rand($utilisateurs)])
                   ->setGroupe($groupes[array_rand($groupes)]);
                   
            for ($l=0 ; $l<mt_rand(0, 5) ; $l++)
            {
                $illustration = new Illustration();
                
                $illustration->setUrl($faker->imageUrl(720, 480))
                ->setAlt($faker->sentence())
                ->setFigure($figure);
                
                $manager->persist($illustration);
            }
            
            $fakerYoutube = Factory::create();
            $fakerYoutube->addProvider(new Youtube($faker));
                    
            for ($m=0 ; $m<mt_rand(0, 5) ; $m++)
            {
                $video = new Video();

                $video->setUrl($fakerYoutube->youtubeEmbedUri())
                      ->setAlt($faker->sentence())
                      ->setFigure($figure);
                
                $manager->persist($video);
            }
            
            for ($n=0 ; $faker->numberBetween(0, 30) ; $n++)
            {
                $commentaire = new Commentaire();

                $commentaire->setDateCreation(new \DateTime())
                            ->setContenu(implode(" ", $faker->sentences()))
                            ->setAuteur($utilisateurs[array_rand($utilisateurs)])
                            ->setFigure($figure);
                
                $manager->persist($commentaire);
            }
            
            $manager->persist($figure);
            array_push($figures, $figure);
        }

        $manager->flush();
    }
}
