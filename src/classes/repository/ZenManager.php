<?php

namespace projet\classes\repository;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use projet\models\Cabine;
use projet\models\Hotesse;
use projet\models\Reservation;
use projet\models\Service;

/**
 * Class ZenManager
 * Gère les interactions avec la base de données.
 */
class ZenManager {

    /**
     * Crée une réservation pour une cabine donnée.
     *
     * @param int $numCabine ID de la cabine
     * @param string $dateHeure Date et heure de réservation
     * @param int $nbPersonnes Nombre de personnes
     * @return Reservation La réservation créée
     * @throws Exception Si cabine introuvable, capacité insuffisante ou déjà réservée
     */
    public static function reserverCabine(int $numCabine, string $dateHeure, int $nbPersonnes) : Reservation {
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

    /**
     * Ajoute un service à une réservation existante.
     *
     * @param int $numRes ID de la réservation
     * @param int $numServ ID du service
     * @param int $quantite Quantité demandée
     * @throws Exception Si stock insuffisant ou réservation déjà payée.
     */
    public static function commanderService(int $numRes, int $numServ, int $quantite) : void {
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

            if ($reservation->datpaie !== null) {
                throw new Exception("ERREUR : Impossible d'annuler : réservation déjà payée/consommée.");
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

    /**
     * Affecte une hôtesse à une cabine.
     *
     * @param int $numHot ID de l'hôtesse
     * @param int $numCab ID de la cabine
     * @throws Exception Si la cabine a déjà une hôtesse
     */
    public static function affecterHotesse(int $numHot, int $numCab) : void {
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

    /**
     * Calcule le total et enregistre le paiement
     *
     * @param int $numRes ID Réservation
     * @param string $modePaiement Mode
     * @return float Le montant total calculé
     * @throws Exception Si déjà payé.
     */
    public static function encaisserReservation(int $numRes, string $modePaiement) : float {
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

    /**
     * Annule et supprime une réservation
     *
     * @param int $numRes ID Réservation
     * @throws Exception Si la réservation est déjà payée.
     */
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

            //Suppression de la réservation elle-meme
            $reservation->delete();

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Modifie les propriétés d'un service
     *
     * @param int $numServ ID Service
     * @param float|null $nouveauPrix Nouveau prix
     * @param int|null $nouveauStock Nouveau stock
     * @throws Exception Si le service n'a pas été trouvé
     */
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