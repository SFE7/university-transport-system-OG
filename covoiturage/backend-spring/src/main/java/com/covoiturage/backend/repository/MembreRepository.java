package com.covoiturage.backend.repository;

import com.covoiturage.backend.entity.Membre;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

public interface MembreRepository extends JpaRepository<Membre, Long> {
    Optional<Membre> findByEmail(String email);
}

