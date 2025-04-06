-- Sélectionner la base de données où les données doivent être insérées

USE php;

-- Insertion des catégories de compétences
INSERT INTO category (category_id, category_name)
VALUES
    (1, 'Programmation'),
    (2, 'Musique'),
    (3, 'Mathématiques'),
    (4, 'Design graphique'),
    (5, 'Photographie'),
    (6, 'Langues étrangères'),
    (7, 'Marketing digital'),
    (8, 'Rédaction'),
    (9, 'Gestion de projet'),
    (10, 'Cuisine');

-- Insertion des utilisateurs
-- MDP des users : "pass"
INSERT INTO app_user (user_id, mail, user_first_name, user_last_name, biography, phone, password, balance, avatar_path)
VALUES
    (1, 'alice@example.com', 'Alice', 'Dupont', 'Développeuse Python passionnée.', '0101010101', '$2y$10$PzrLrAzoR9garXQzFTyoxuYxHTNbUP3PKIHv2N6Oc4Cu85ZXqatZu', 100, '/assets/avatars/avatar-default.png'),
    (2, 'bob@example.com', 'Bob', 'Martin', 'Musicien, expert guitare et piano.', '0202020202', '$2y$10$PzrLrAzoR9garXQzFTyoxuYxHTNbUP3PKIHv2N6Oc4Cu85ZXqatZu', 100, '/assets/avatars/avatar-default.png'),
    (3, 'charlie@example.com', 'Charlie', 'Durand', 'Professeur de mathématiques, spécialiste de l algèbre et calcul.', '0303030303', '$2y$10$PzrLrAzoR9garXQzFTyoxuYxHTNbUP3PKIHv2N6Oc4Cu85ZXqatZu', 100, '/assets/avatars/avatar-default.png'),
    (4, 'david@example.com', 'David', 'Lemoine', 'Étudiant en gestion de projet.', '0404040404', '$2y$10$PzrLrAzoR9garXQzFTyoxuYxHTNbUP3PKIHv2N6Oc4Cu85ZXqatZu', 100, '/assets/avatars/avatar-default.png'),
    (5, 'eva@example.com', 'Eva', 'Petit', 'Photographe amateur, passionnée par la cuisine.', '0505050505', '$2y$10$PzrLrAzoR9garXQzFTyoxuYxHTNbUP3PKIHv2N6Oc4Cu85ZXqatZu', 100, '/assets/avatars/avatar-default.png'),
    (6, 'francois@example.com', 'François', 'Lemoine', 'Apprenant de l espagnol et gestion de projet.', '0606060606', '$2y$10$PzrLrAzoR9garXQzFTyoxuYxHTNbUP3PKIHv2N6Oc4Cu85ZXqatZu', 100, '/assets/avatars/avatar-default.png');

-- Insertion des compétences
INSERT INTO skill (skill_id, skill_name, search_counter, category_id)
VALUES
    (1, 'Python', 0, 1),
    (2, 'JavaScript', 0, 1),
    (3, 'Guitare', 0, 2),
    (4, 'Piano', 0, 2),
    (5, 'Calcul différentiel', 0, 3),
    (6, 'Algèbre', 0, 3),
    (7, 'Photoshop', 0, 4),
    (8, 'Photographie numérique', 0, 5),
    (9, 'Espagnol', 0, 6),
    (10, 'Gestion de projet agile', 0, 9);

-- Insertion des lieux (locations)
INSERT INTO location (location_id, address, zip_code, city)
VALUES
    (1, '10 Rue de Paris', '44100', 'Nantes'), -- Lieu 1
    (2, 'Cité des Congrès - 5 Rue de Valmy', '44000', 'Nantes'), -- Lieu 2
    (3, '50 Rue Julien Douillard', '44400', 'Rezé'), -- Lieu 3
    (4, '1 Rue Floréal', '44300', 'Nantes'); -- Lieu 4

-- Insertion des sessions
INSERT INTO session (session_id, start_time, end_time, date_session, description, skill_taught_id)
VALUES
    (1, '09:00:00', '12:00:00', '2025-05-01', 'Cours de Python pour débutants', 1), -- Python (session 1)
    (2, '10:00:00', '12:00:00', '2025-05-02', 'Échange de compétences sur la guitare', 3), -- Guitare (session 2)
    (3, '14:00:00', '16:00:00', '2025-05-03', 'Echange dalgèbre', 6), -- Algèbre (session 3)
    (4, '09:00:00', '11:00:00', '2025-05-04', 'Échange de compétences sur le piano', 4), -- Piano (session 4)
    (5, '13:00:00', '15:00:00', '2025-05-05', 'Cours de photographie numérique', 8), -- Photographie numérique (session 5)
    (6, '09:00:00', '12:00:00', '2025-05-06', 'Cours sur la gestion de projet agile', 10); -- Gestion de projet agile (session 6)

-- Lier les sessions avec les leçons
INSERT INTO lesson (lesson_session_id, location_id, lesson_host_id, max_attendees)
VALUES
    (1, 4, 1, 5), -- Cours Python (Session 1)
    (5, 1, 5, 10), -- Cours Photographie numérique (Session 5)
    (6, 2, 6, 10); -- Cours Gestion de projet agile (Session 6)

-- Lier les sessions avec les échanges
INSERT INTO exchange (exchange_session_id, skill_requested_id, exchange_requester_id, exchange_accepter_id)
VALUES
    (2, 6, 2, 4), -- Échange de compétences Guitare (Session 2)
    (3, 2, 3, NULL), -- Échange de compétences Algèbre (Session 3)
    (4, 9, 2, 6); -- Échange de compétences Piano (Session 4)


-- Participation des utilisateurs aux leçons et échanges

-- Session 1 (Cours Python)
INSERT INTO attend (attend_id, attend_lesson_id, attend_user_id)
VALUES
    (1, 1, 4); -- Utilisateur 4 participe à Cours Python (Session 1)


-- Session 5 (Cours Photographie numérique)
INSERT INTO attend (attend_id, attend_lesson_id, attend_user_id)
VALUES
    (2, 5, 6); -- Utilisateur 6 participe à Cours Photographie numérique (Session 5)

-- Session 6 (Cours Gestion de projet agile)
INSERT INTO attend (attend_id, attend_lesson_id, attend_user_id)
VALUES
    (3, 6, 5); -- Utilisateur 5 participe à Cours Gestion de projet agile (Session 6)
