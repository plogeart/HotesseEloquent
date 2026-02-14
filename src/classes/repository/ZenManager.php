<?php

namespace projet\classes\repository;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use projet\models\Cabine;
use projet\models\Hotesse;
use projet\models\Reservation;
use projet\models\Service;

class ZenManager {

    public static function reserverCabine(int $numCabine, string $dateHeure, int $nbPersonnes) {
        DB::beginTransaction();

        try {
            //Sélectionne la cabine et la verrouille
            $cabine = Cabine::where('numcab', $numCabine)->lockForUpdate()->first();

            if (!$cabine) {
                throw new Exception("Cabine introuvable");
            }

            if ($cabine->nbplace < $nbPersonnes) {
                throw new Exception("Capacité insuffisante");
            }

            //test si une ligne existe déjà pour cette cabine à cette heure
            $existe = Reservation::where('numcab', $numCabine)->where('datres', $dateHeure)->exists();

            if ($existe) {
                throw new Exception("Cabine déjà réservée à cette date");
            }

            //Calcul de l'id
            $maxId = Reservation::max('numres');
            $nextId = $maxId ? $maxId + 1 : 1;

            //Création et sauvegarde de l'objet Réservation
            $reservation = new Reservation();
            $reservation->numres = $nextId;
            $reservation->numcab = $numCabine;
            $reservation->datres = $dateHeure;
            $reservation->nbpers = $nbPersonnes;

            $reservation->save();

            DB::commit();
            return $reservation;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public static function commanderService(int $numRes, int $numServ, int $quantite) {
        try {
            DB::beginTransaction();

            //Récupère le service et le verrouille
            $service = Service::lockForUpdate()->find($numServ);

            if (!$service) {
                throw new Exception("Service introuvable.");
            }

            if ($service->nbrinterventions < $quantite) {
                throw new Exception("Disponibilité insuffisante pour ce service.");
            }

            $reservation = Reservation::find($numRes);
            if (!$reservation) {
                throw new Exception("Réservation introuvable.");
            }

            //Maj du stock
            $service->nbrinterventions -= $quantite;
            $service->save();

            //Création du lien dans la table pivot
            $reservation->services()->attach($numServ, ['nbrinterevntions' => $quantite]);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function affecterHotesse(int $numHot, int $numCab) {
        try {
            DB::beginTransaction();

            $hotesse = Hotesse::find($numHot);
            $cabine = Cabine::find($numCab);

            if (!$hotesse || !$cabine) {
                throw new Exception("Hôtesse ou Cabine introuvable.");
            }

            //On vérifie si la cabine est déjà assignée à quelqu'un d'autre
            $existe = DB::table('affecter')->where('numcab', $numCab)->count();

            if ($existe > 0) {
                throw new Exception("Cette cabine a déjà une hôtesse affectée.");
            }

            //Création du lien
            $hotesse->cabines()->attach($numCab);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function encaisserReservation(int $numRes, string $modePaiement) {
        try {
            DB::beginTransaction();

            //on récupère la réservation et tous ses services associés
            $reservation = Reservation::with('services')->find($numRes);

            if (!$reservation) {
                throw new Exception("Réservation introuvable.");
            }

            if ($reservation->datpaie != null) {
                throw new Exception("Cette réservation est déjà payée.");
            }

            $montantTotal = 0;

            //Boucle sur les services
            foreach ($reservation->services as $service) {
                $quantite = $service->pivot->nbrinterevntions;
                $prix = $service->prixunit;
                $montantTotal += ($quantite * $prix);
            }

            //Maj des infos de paiement
            $reservation->montcom = $montantTotal;
            $reservation->modpaie = $modePaiement;
            $reservation->datpaie = date('Y-m-d H:i:s');
            $reservation->save();

            DB::commit();
            return $montantTotal;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function annulerReservation(int $numRes) {
        try {
            DB::beginTransaction();
            $reservation = Reservation::find($numRes);

            if (!$reservation) {
                throw new Exception("Réservation introuvable.");
            }

            //on ne supprime pas si c'est déjà payé.
            if ($reservation->datpaie != null) {
                throw new Exception("Impossible d'annuler : réservation déjà payée/consommée.");
            }

            //Suppression des liens dans la table pivot
            $reservation->services()->detach();

            //Suppression de la réservation elle-même
            $reservation->delete();

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function modifierService(int $numServ, ?float $nouveauPrix, ?int $nouveauStock) {
        try {
            //Récupération
            $service = Service::find($numServ);
            if (!$service) {
                throw new Exception("Service introuvable.");
            }

            //Modif des attributs de l'objet
            if ($nouveauPrix !== null) {
                $service->prixunit = $nouveauPrix;
            }
            if ($nouveauStock !== null) {
                $service->nbrinterventions = $nouveauStock;
            }

            $service->save();

        } catch (Exception $e) {
            throw $e;
        }
    }
}