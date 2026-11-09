package com.covoiturage.backend.repository;

import com.covoiturage.backend.entity.Notification;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

public interface NotificationRepository extends JpaRepository<Notification, Long> {
    Page<Notification> findByMembreIdOrderByIsReadAscCreatedAtDesc(Long membreId, Pageable pageable);
}

