# 🧭 React + TypeScript Roadmap – Backend → Frontend maîtrisé

> Profil cible : Développeur PHP/Symfony expérimenté découvrant React
> Objectif : Monter en compétence React **sans perdre la rigueur backend**
> Contexte : Symfony 6.4 + Webpack Encore + React 18 + Docker

---

## 📌 Sommaire

1. 🎯 Principes directeurs
2. 🧠 Patterns React – À maîtriser / À ignorer
3. 🗺️ Mapping dans le repo actuel
4. 📅 Plan détaillé par semaine (checklists actionnables)

---

## 1. 🎯 Principes directeurs

- ✅ **Progressivité avant perfection**
- ✅ **Typage comme outil d’intention, pas comme contrainte**
- ✅ **Tests sur la logique avant l’UI**
- ✅ **Front traité comme un produit, pas comme un script**
- ✅ **Toujours garder une app déployable**

---

## 2. 🧠 Patterns React

### ✅ Patterns à MAÎTRISER absolument

#### 🧩 1. Composants fonctionnels purs

- Un composant = une responsabilité
- Pas de logique métier lourde dans le JSX
- Props simples, explicites

#### 🔁 2. Hooks personnalisés

- Extraire la logique de :
  - fetch
  - pagination
  - filtrage
  - états complexes
- Tester les hooks indépendamment des composants

#### 🧠 3. State local vs state dérivé

- `useState` uniquement pour :
  - interactions utilisateur
  - états transitoires
- Calculs = fonctions pures

#### 🧱 4. Error Boundaries

- Protéger les zones critiques
- Fallback UI clair
- Log + récupération

#### 🧪 5. Tests orientés comportement

- Tester :
  - ce que l’utilisateur fait
  - ce qu’il voit
- Pas l’implémentation interne

---

### ❌ Patterns à IGNORER (pour l’instant)

- ❌ Redux / MobX
- ❌ Context global partout
- ❌ HOCs complexes
- ❌ Micro-optimisations (`useMemo`, `useCallback`) prématurées
- ❌ Typage excessif (génériques imbriqués)

---

## 3. 🗺️ Mapping dans le repo actuel

### Structure cible (progressive)

assets/
├── app.js # point d’entrée React
├── components/ # composants UI purs
│ ├── ClanSearchForm.jsx
│ ├── ErrorBoundary.jsx
│ └── ui/
├── hooks/ # logique métier frontend
│ ├── useClanSearch.ts
│ └── useApi.ts
├── services/ # appels HTTP
│ └── clanApi.ts
├── types/ # types métier
│ └── clan.ts
└── styles/

### Philosophie

- **Symfony = backend métier**
- **React = orchestration UI**
- **Hooks = logique**
- **Services = infra**

---

## 4. 📅 Plan détaillé par semaine

---

## 🗓️ Semaine 1 – TypeScript progressif

### 🎯 Objectif

Comprendre TypeScript **sans casser la vélocité**

### ✅ Checklist

- [ ] Ajouter `tsconfig.json` pragmatique
- [ ] Migrer 5–7 fichiers maximum :
  - 1 composant simple
  - 1 hook
  - 1 utilitaire
  - 1 service API
- [ ] Accepter `any` temporairement
- [ ] Comprendre les erreurs TS (pas les corriger aveuglément)

### ✅ Livrable

- Build OK
- Types compréhensibles
- Aucun refactor massif

---

## 🗓️ Semaine 2 – TypeScript consolidation & intention métier

### 🎯 Objectif

Utiliser TS là où il **a du sens**

### ✅ Checklist

- [ ] Tous les fichiers compilent
- [ ] Définir les types métier (`types/`)
- [ ] Typer :
  - réponses API
  - props publiques
- [ ] Laisser non typé :
  - logique UI triviale
- [ ] Documenter les `any` restants

### ✅ Livrable

- Typage lisible
- Aucune dette cachée
- Code explicable à l’oral

---

## 🗓️ Semaine 3 – Tests React (Hooks)

### 🎯 Objectif

Sécuriser la logique avant l’UI

### ✅ Checklist

- [ ] Installer Jest / Testing Library
- [ ] Tester chaque hook clé :
  - état initial
  - transitions
  - erreurs
- [ ] Mock API proprement
- [ ] Éviter les snapshots

### ✅ Livrable

- ≥20 tests utiles
- Hooks testés indépendamment

---

## 🗓️ Semaine 4 – Tests composants & Error Boundaries

### 🎯 Objectif

Rendre l’UI robuste

### ✅ Checklist

- [ ] Créer au moins une Error Boundary
- [ ] Tester les composants critiques :
  - formulaire
  - affichage résultats
- [ ] Tester les cas d’erreur utilisateur
- [ ] Vérifier accessibilité minimale

### ✅ Livrable

- UI protégée
- Fallback visible et testé

---

## 🗓️ Semaine 5 – Infra & CI/CD (Raspberry Pi)

### 🎯 Objectif

Déployer réellement l’app

### ✅ Checklist

- [ ] Docker build OK
- [ ] CI :
  - build
  - tests
- [ ] Déploiement auto sur Pi
- [ ] Logs accessibles

### ✅ Livrable

- App accessible en ligne
- Pipeline reproductible

---

## 🗓️ Semaine 6 – Sécurité web

### 🎯 Objectif

Rendre l’app défendable

### ✅ Checklist

- [ ] HTTPS (certificat valide)
- [ ] Headers de sécurité
- [ ] Scan basique (OWASP)
- [ ] Documentation sécurité

### ✅ Livrable

- App sécurisée
- Rapport court et clair

---

## ✅ Résultat final attendu

- App React typée **avec discernement**
- Tests utiles, pas décoratifs
- Déploiement réel
- Vision claire front / back

> 🎯 Objectif atteint : **développeur fullstack moderne, sans perte de séniorité**
