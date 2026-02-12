<?php

namespace projet\classes;

use Illuminate\Database\Capsule\Manager as DB;
use projet\models\Reservation;
use projet\models\Service;
use projet\models\Cabine;
use projet\models\Hotesse;
use Exception;

class ZenManager {

    public function reserverCabine(int $numCabine, string $dateHeure, int $nbPersonnes) {
        DB::beginTransaction();

        try {
            $cabine = Cabine::where('numcab', $numCabine)->lockForUpdate()->first();

            if (!$cabine) {
                throw new Exception("Cabine introuvable");
            }

            if ($cabine->nbplace < $nbPersonnes) {
                throw new Exception("Capacité insuffisante");
            }

            $existe = Reservation::where('numcab', $numCabine)
                ->where('datres', $dateHeure)
                ->exists();

            if ($existe) {
                throw new Exception("Cabine déjà réservée à cette date");
            }

            $maxId = Reservation::max('numres');
            $nextId = $maxId ? $maxId + 1 : 1;

            $reservation = new Reservation();
            $reservation->numres = $nextId;
            $reservation->numcab = $numCabine;
            $reservation->datres = $dateHeure;
            $reservation->nbpers = $nbPersonnes;
            
            $reservation->timestamps = false; 

            $reservation->save();

            DB::commit();

            return $reservation;

        } catch (Exception $e) {
            DB::rollback();
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

    public function annulerReservation(int $numRes) {
        try {
            DB::beginTransaction();
            $reservation = Reservation::find($numRes);
            
            if (!$reservation) {
                throw new Exception("Réservation introuvable.");
            }
            
            if ($reservation->datpaie != null) {
                throw new Exception("Impossible d'annuler : réservation déjà payée/consommée.");
            }
            
            $reservation->services()->detach();
            $reservation->delete();
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function modifierService(int $numServ, ?float $nouveauPrix, ?int $nouveauStock) {
        try {
            $service = Service::find($numServ);
            if (!$service) {
                throw new Exception("Service introuvable.");
            }

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

    public function login(int $id, string $password) {
        $h = Hotesse::find($id);
        if (!$h) {
            throw new Exception("Identifiant inconnu.");
        }
        if ($h->passwd !== $password) {
            throw new Exception("Mot de passe incorrect.");
        }
        return $h;
    }
}