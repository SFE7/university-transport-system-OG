package com.covoiturage.backend.repository;

import com.covoiturage.backend.entity.Reservation;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ReservationRepository extends JpaRepository<Reservation, Long> {
    Page<Reservation> findByPassagerId(Long membreId, Pageable pageable);

    boolean existsByPassagerIdAndTrajetId(Long membreId, Long trajetId);
}

