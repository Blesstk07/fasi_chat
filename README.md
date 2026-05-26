# 🏛️ Campus Relay - FasiChat Classroom

Plateforme de messagerie académique interne pour la Faculté des Sciences de l'Information (FASI).

## 📋 Description

Campus Relay est une application web complète de communication interne développée en PHP Orienté Objet. Elle permet aux membres d'une même promotion (étudiants, enseignants, assistants) ainsi qu'aux responsables administratifs (Doyen, Vice-Doyen, Apparitaire) de communiquer de manière structurée et sécurisée.

## 🚀 Fonctionnalités

### Pour tous les utilisateurs
- ✅ Authentification sécurisée (sessions PHP)
- ✅ Tableau de bord personnalisé selon le rôle
- ✅ Messagerie privée et de groupe
- ✅ Upload de fichiers (images, vidéos, PDF, DOC)
- ✅ Compression automatique des images (GD)
- ✅ Consultation du Valve (annonces officielles)

### Pour Doyen et Vice-Doyen
- ✅ Envoi de convocations à tous les enseignants/assistants
- ✅ Vision globale de la plateforme
- ✅ Interface d'administration

### Pour Enseignants et Assistants
- ✅ Publication sur le mur pédagogique
- ✅ Consultation des convocations reçues
- ✅ Messagerie avec les étudiants

### Pour Apparitaire
- ✅ CRUD complet des annonces sur le Valve
- ✅ Gestion des catégories (urgent, convocation, information, académique)
- ✅ Gestion des priorités

### Pour Étudiants
- ✅ Messagerie privée entre étudiants de même promotion
- ✅ Consultation du mur pédagogique (lecture seule)
- ✅ Consultation du Valve

## 🏗️ Architecture Technique

### Stack utilisée
- **Backend** : PHP 8.3 (POO native)
- **Base de données** : MySQL / MariaDB
- **Frontend** : HTML5, CSS3, JavaScript
- **Bibliothèques** : GD (compression d'images)

### Structure POO
Utilisateur (abstract)
├── Etudiant
├── Enseignant
│ └── Assistant (hérite de Enseignant)
├── Doyen (utilise ConvocableTrait)
├── ViceDoyen (utilise ConvocableTrait)
└── Apparitaire

### Design Pattern
- **Singleton** : BaseDeDonnees
- **Factory** : Création d'utilisateurs selon le rôle
- **Trait** : ConvocableTrait pour Doyen/ViceDoyen
- **MVC** : Architecture modulaire

## Comptes de test

**Rôle**	**Email** **Mot de passe**
Doyen	doyen@fasi.edu	password123
Vice-doyen	vdoyen@fasi.edu	password123
Apparitaire	apparitaire@fasi.edu	password123
Enseignant	enseignant@fasi.edu	password123
Assistant	assistant@fasi.edu	password123
Étudiant	blesstk@fasi.edu	password123
Étudiant	jfrex@fasi.edu	password123
Étudiant	rubilax@fasi.edu	password123
Étudiant	esther@fasi.edu	password123
Étudiant	henriette@fasi.edu	password123

## Structure du projet

CampusRelay/
├── index.php                 # Tableau de bord principal
├── login.php                 # Authentification
├── logout.php                # Déconnexion
├── messagerie.php            # Messagerie complète
├── valve.php                 # Valve (annonces officielles)
├── valve_admin.php           # Administration du Valve
├── mur_pedagogique.php       # Mur pédagogique
├── convocation.php           # Système de convocation
├── classes/
│   ├── modeles/              # Classes métier (13 classes)
│   ├── services/             # Services (BDD, Auth)
│   └── traits/               # Traits POO
├── config/                   # Configuration
├── assets/                   # CSS, JS, images
├── uploads/                  # Fichiers uploadés
└── sql/                      # Scripts SQL

##  Base de données

**Table**	**Description**
utilisateur	-> Utilisateurs (6 rôles)
conversations	->Conversations
messages ->	Messages texte/fichiers
fichiers ->	Fichiers joints avec compression
annonces ->	Annonces du Valve
publications_mur ->	Mur pédagogique
reunions ->	Convocations

## Équipe Campus Relay
**Nom**	 **Rôle**
Tshimanga Kalala Bless	Développeur Backend & Frontend
Mumpubi Elam Rubis	Développeur Base de données
Civava Litsani Esther	Documentation



