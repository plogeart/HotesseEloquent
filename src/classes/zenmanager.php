<?php

namespace classes;

use Illuminate\Database\Capsule\Manager as DB;
use models\Reservation;
use models\Service;
use models\Cabine;
use models\Hotesse;
use Exception;

class ZenManager {

    public function reserverCabine(int $numCabine, string $dateHeure, int $nbPersonnes) {
        try {
            DB::beginTransaction();

            $count = Reservation::where('numcab', $numCabine)
                ->where('datres', $dateHeure)
                ->count();

            if ($count > 0) {
                throw new Exception("La cabine est déjà réservée pour cette date.");
            }

            $reservation = new Reservation();
            $reservation->numcab = $numCabine;
            $reservation->datres = $dateHeure;
            $reservation->nbpers = $nbPersonnes;
            $reservation->save();

            DB::commit();
            return $reservation;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function commanderService(int $numRes, int $numServ, int $quantite) {
        try {
            DB::beginTransaction();

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

            $service->nbrinterventions -= $quantite;
            $service->save();

            $reservation->services()->attach($numServ, ['nbrinterevntions' => $quantite]);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function affecterHotesse(int $numHot, int $numCab) {
        try {
            DB::beginTransaction();

            $hotesse = Hotesse::find($numHot);
            $cabine = Cabine::find($numCab);

            if (!$hotesse || !$cabine) {
                throw new Exception("Hôtesse ou Cabine introuvable.");
            }

            $existe = DB::table('affecter')
                ->where('numcab', $numCab)
                ->count();

            if ($existe > 0) {
                throw new Exception("Cette cabine a déjà une hôtesse affectée.");
            }

            $hotesse->cabines()->attach($numCab);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function encaisserReservation(int $numRes, string $modePaiement) {
        try {
            DB::beginTransaction();

            $reservation = Reservation::with('services')->find($numRes);

            if (!$reservation) {
                throw new Exception("Réservation introuvable.");
            }

            if ($reservation->datpaie != null) {
                throw new Exception("Cette réservation est déjà payée.");
            }

            $montantTotal = 0;

            foreach ($reservation->services as $service) {
                $quantite = $service->pivot->nbrinterevntions;
                $prix = $service->prixunit;
                $montantTotal += ($quantite * $prix);
            }

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
}