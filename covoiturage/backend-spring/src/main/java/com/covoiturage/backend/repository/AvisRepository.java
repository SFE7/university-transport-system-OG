package com.covoiturage.backend.repository;

import com.covoiturage.backend.entity.Avis;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

public interface AvisRepository extends JpaRepository<Avis, Long> {
    Page<Avis> findByConducteurId(Long conducteurId, Pageable pageable);

    boolean existsByReviewerIdAndTrajetId(Long reviewerId, Long trajetId);
}

