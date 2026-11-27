<?php

namespace App\DataFixtures;

use App\Entity\Boisson;
use App\Entity\Commande;
use App\Entity\Media;
use App\Entity\User;
use App\Enum\StatutCommande;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création des utilisateurs
        $patron = new User();
        $patron->setUsername('patron');
        $patron->setRoles(['ROLE_PATRON']);
        $patron->setPassword($this->passwordHasher->hashPassword($patron, 'patron123'));
        $manager->persist($patron);

        $barman = new User();
        $barman->setUsername('barman');
        $barman->setRoles(['ROLE_BARMAN']);
        $barman->setPassword($this->passwordHasher->hashPassword($barman, 'barman123'));
        $manager->persist($barman);

        $serveur = new User();
        $serveur->setUsername('mustapha-serveur');
        $serveur->setRoles(['ROLE_SERVEUR']);
        $serveur->setPassword($this->passwordHasher->hashPassword($serveur, 'serveur123'));
        $manager->persist($serveur);

        // 2. Création des médias et boissons
        $boissonsData = [
            ['nom' => 'Mojito', 'prix' => '8.50', 'chemin' => '/uploads/media/mojito.jpg'],
            ['nom' => 'Caïpirinha', 'prix' => '9.00', 'chemin' => '/uploads/media/caipirinha.jpg'],
            ['nom' => 'Bière Blonde Pression', 'prix' => '4.50', 'chemin' => '/uploads/media/biere.jpg'],
            ['nom' => 'Coca-Cola', 'prix' => '3.50', 'chemin' => '/uploads/media/coca.jpg'],
            ['nom' => 'Café Espresso', 'prix' => '2.00', 'chemin' => '/uploads/media/cafe.jpg'],
        ];

        $boissonsEntities = [];
        foreach ($boissonsData as $item) {
            $media = new Media();
            $media->setChemin($item['chemin']);
            $manager->persist($media);

            $boisson = new Boisson();
            $boisson->setNom($item['nom']);
            $boisson->setPrix($item['prix']);
            $boisson->setPhoto($media);
            $manager->persist($boisson);

            $boissonsEntities[] = $boisson;
        }

        // 3. Création de quelques commandes d'exemple avec différents statuts
        // Commande 1 : En cours de préparation
        $commande1 = new Commande();
        $commande1->setNumeroTable(4);
        $commande1->setServeur($serveur);
        $commande1->setStatus(StatutCommande::EN_COURS_PREPARATION);
        $commande1->setCreatedAt(new \DateTimeImmutable('-2 hours'));
        $commande1->addBoisson($boissonsEntities[0]); // Mojito
        $commande1->addBoisson($boissonsEntities[2]); // Bière
        $manager->persist($commande1);

        // Commande 2 : Prête et prise par le barman
        $commande2 = new Commande();
        $commande2->setNumeroTable(2);
        $commande2->setServeur($serveur);
        $commande2->setBarman($barman);
        $commande2->setStatus(StatutCommande::PRETE);
        $commande2->setCreatedAt(new \DateTimeImmutable('-1 hour'));
        $commande2->addBoisson($boissonsEntities[1]); // Caïpirinha
        $commande2->addBoisson($boissonsEntities[3]); // Coca
        $manager->persist($commande2);

        // Commande 3 : Payée
        $commande3 = new Commande();
        $commande3->setNumeroTable(7);
        $commande3->setServeur($serveur);
        $commande3->setBarman($barman);
        $commande3->setStatus(StatutCommande::PAYEE);
        $commande3->setCreatedAt(new \DateTimeImmutable('-30 minutes'));
        $commande3->addBoisson($boissonsEntities[4]); // Café
        $manager->persist($commande3);

        $manager->flush();
    }
}
