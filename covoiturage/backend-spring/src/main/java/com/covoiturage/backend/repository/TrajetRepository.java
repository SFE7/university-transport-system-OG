package com.covoiturage.backend.repository;

import com.covoiturage.backend.entity.Trajet;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface TrajetRepository extends JpaRepository<Trajet, Long> {

    Page<Trajet> findByDeparturePointContainingAndArrivalPointContaining(
            String departurePoint, String arrivalPoint, Pageable pageable);

    @Query("select t from Trajet t where t.conducteur.id = :membreId")
    Page<Trajet> findByMembreId(@Param("membreId") Long membreId, Pageable pageable);
}

