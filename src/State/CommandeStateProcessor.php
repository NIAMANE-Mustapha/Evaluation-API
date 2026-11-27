<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Commande;
use App\Entity\User;
use App\Enum\StatutCommande;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class CommandeStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $innerProcessor,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Commande) {
            $currentUser = $this->security->getUser();

            // Création d'une commande (POST)
            if ($operation instanceof Post) {
                $data->setCreatedAt(new \DateTimeImmutable());
                $data->setStatus(StatutCommande::EN_COURS_PREPARATION);

                if ($currentUser instanceof User) {
                    $data->setServeur($currentUser);
                }
            } else {
                // Modification d'une commande (PUT / PATCH)
                $previousData = $context['previous_data'] ?? null;

                if ($previousData instanceof Commande) {
                    // Règle : impossible de modifier une commande déjà payée
                    if ($previousData->getStatus() === StatutCommande::PAYEE) {
                        throw new BadRequestHttpException('Impossible de modifier une commande déjà payée.');
                    }

                    $isServeur = $this->security->isGranted('ROLE_SERVEUR');
                    $isBarman = $this->security->isGranted('ROLE_BARMAN');
                    $isPatron = $this->security->isGranted('ROLE_PATRON');

                    // Vérifications pour un Barman
                    if ($isBarman && !$isPatron) {
                        // Le barman ne peut pas modifier les boissons d'une commande (réservé au serveur)
                        if ($data->getBoissons()->getValues() !== $previousData->getBoissons()->getValues() && !$isServeur) {
                            throw new AccessDeniedHttpException('Seul un serveur peut modifier les boissons d\'une commande.');
                        }

                        // Le barman ne peut pas enregistrer le paiement (réservé au serveur)
                        if ($data->getStatus() === StatutCommande::PAYEE && !$isServeur) {
                            throw new AccessDeniedHttpException('Seul un serveur peut enregistrer le paiement d\'une commande.');
                        }

                        // Le barman peut s'attribuer la commande
                        if ($data->getBarman() !== null && $currentUser instanceof User) {
                            $data->setBarman($currentUser);
                        }
                    }

                    // Vérifications pour un Serveur
                    if ($isServeur && !$isPatron && !$isBarman) {
                        // Le serveur ne peut pas passer une commande au statut prête (réservé au barman)
                        if ($data->getStatus() === StatutCommande::PRETE) {
                            throw new AccessDeniedHttpException('Seul un barman peut marquer une commande comme prête.');
                        }

                        // Le serveur ne peut pas modifier l'attribution du barman
                        if ($data->getBarman() !== $previousData->getBarman()) {
                            throw new AccessDeniedHttpException('Seul un barman peut s\'attribuer une commande.');
                        }
                    }
                }
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
