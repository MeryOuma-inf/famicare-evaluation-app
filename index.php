<?php
/**
 * index.php — Page d'accueil + connexion FamiCare
 * MODIFIÉ semaine 4 : formulaire pointe vers login.php
 * Les erreurs arrivent via $_SESSION['login_erreur']
 */
session_start();
require_once __DIR__ . '/config.php';

// Rediriger si déjà connecté
if (isset($_SESSION['utilisateur'])) {
    header('Location: ' . BASE_URL . ($_SESSION['utilisateur']['role'] === 'admin' ? 'admin/dashboard.php' : 'intervenante/accueil.php'));
    exit;
}

// Récupérer l'erreur depuis la session (envoyée par login.php)
$erreur = $_SESSION['login_erreur'] ?? '';
unset($_SESSION['login_erreur']); // Effacer après affichage
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FamiCare — Espace Évaluation des Intervenantes</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    /* ===== VARIABLES ===== */
    :root {
      --blanc:       #FFFFFF;
      --rose:        #F5E4EB;
      --bleu:        #D1DCE9;
      --bleu-d:      #B8CAE0;
      --jaune:       #F7E597;
      --noir:        #2A2727;
      --noir-l:      #5A5555;
      --muted:       #9A9494;
      --white:       #FFFFFF;
      --border:      #E8E0E0;
    }

    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--blanc);
      color: var(--noir);
      font-size: 15px;
      line-height: 1.65;
    }

    /* ===== UTILITAIRES ===== */
    .container {
      max-width: 1160px;
      margin: 0 auto;
      padding: 0 40px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 26px;
      border-radius: 50px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      text-decoration: none;
      transition: all .22s;
    }
    .btn-primary { background: var(--bleu); color: var(--noir); border: 1.5px solid var(--bleu-d); }
    .btn-primary:hover { background: var(--bleu-d); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(209,220,233,.35); color: #fff; }
    .btn-outline { background: transparent; color: var(--bleu); border: 2px solid var(--bleu); }
    .btn-outline:hover { background: var(--bleu); color: #fff; }
    .btn-white { background: #fff; color: var(--noir); }
    .btn-white:hover { background: var(--blanc); transform: translateY(-1px); color: var(--noir); }

    .section-label {
      display: inline-block;
      background: var(--jaune);
      color: var(--bleu);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 5px 14px;
      border-radius: 20px;
      margin-bottom: 16px;
    }

    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(32px, 4vw, 48px);
      font-weight: 700;
      color: var(--noir);
      line-height: 1.15;
      margin-bottom: 14px;
    }

    .section-sub {
      font-size: 15px;
      font-weight: 300;
      color: var(--noir-l);
      line-height: 1.7;
      max-width: 560px;
    }

    /* ===== HEADER ===== */
    .site-header {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 900;
      height: 68px;
      background: rgba(245,240,232,.95);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      transition: box-shadow .3s;
    }
    .site-header.scrolled {
      box-shadow: 0 2px 20px rgba(42,39,39,.08);
    }

    .header-inner {
      max-width: 1160px;
      margin: 0 auto;
      padding: 0 40px;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
    }

    .header-logo img {
      height: 38px;
      width: auto;
      display: block;
    }

    .header-nav {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .header-nav a {
      font-size: 14px;
      font-weight: 400;
      color: var(--noir-l);
      text-decoration: none;
      padding: 7px 14px;
      border-radius: 8px;
      transition: all .18s;
    }
    .header-nav a:hover { background: var(--rose); color: var(--noir); }

    .header-right {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* ===== HERO ===== */
    .hero {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      padding-top: 68px;
    }

    .hero-left {
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px 52px 60px 40px;
    }

    .badge-intern {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #C8E6C9;
      color: #2E7D32;
      font-size: 11px;
      font-weight: 600;
      padding: 5px 13px;
      border-radius: 20px;
      margin-bottom: 24px;
      width: fit-content;
    }
    .badge-intern::before {
      content: '';
      width: 6px; height: 6px;
      background: #2E7D32;
      border-radius: 50%;
    }

    .hero-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(38px, 4.5vw, 58px);
      font-weight: 700;
      color: var(--noir);
      line-height: 1.08;
      letter-spacing: -1px;
      margin-bottom: 20px;
    }

    .hero-title em {
      font-style: italic;
      color: var(--bleu);
    }

    .hero-sub {
      font-size: 15px;
      font-weight: 300;
      color: var(--noir-l);
      line-height: 1.7;
      max-width: 420px;
      margin-bottom: 36px;
    }

    .hero-cta-group {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 48px;
      flex-wrap: wrap;
    }

    /* Stats rapides */
    .hero-stats {
      display: flex;
      gap: 0;
      border: 1px solid var(--border);
      border-radius: 16px;
      background: var(--white);
      overflow: hidden;
      width: fit-content;
    }
    .hero-stat {
      padding: 16px 24px;
      text-align: center;
      border-right: 1px solid var(--border);
    }
    .hero-stat:last-child { border-right: none; }
    .hero-stat strong {
      display: block;
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color: var(--bleu);
      line-height: 1;
    }
    .hero-stat span {
      font-size: 11px;
      color: var(--muted);
      font-weight: 300;
      margin-top: 3px;
      display: block;
    }

    /* Feature cards */
    .feature-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-top: 32px;
    }

    .feature-card {
      background: var(--white);
      border-radius: 12px;
      padding: 11px 12px;
      display: flex;
      align-items: center;
      gap: 10px;
      border: 1px solid var(--border);
      transition: all .22s;
    }
    .feature-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(42,39,39,.09); border-color: transparent; }

    .fi {
      width: 36px; height: 36px;
      border-radius: 10px;
      flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .fi svg { width: 20px; height: 20px; }
    .fi-blue   { background: #EFF6FF; }
    .fi-green  { background: #ECFDF5; }
    .fi-purple { background: #F5F3FF; }

    .ft { display: flex; flex-direction: column; gap: 2px; }
    .fl { font-size: 12px; font-weight: 600; color: var(--noir); }
    .fd { font-size: 10px; font-weight: 300; color: var(--muted); }

    /* Hero droite */
    .hero-right {
      position: relative;
      overflow: hidden;
    }
    .hero-right img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center top;
      display: block;
    }
    .hero-right::before {
      content: '';
      position: absolute;
      inset: 0; left: 0; width: 80px;
      background: linear-gradient(to right, var(--blanc), transparent);
      z-index: 2; pointer-events: none;
    }

    .hero-badge {
      position: absolute;
      bottom: 32px; left: 40px;
      background: rgba(255,255,255,.95);
      border-radius: 14px;
      padding: 14px 18px;
      display: flex; align-items: center; gap: 12px;
      box-shadow: 0 4px 20px rgba(42,39,39,.14);
      z-index: 3;
      backdrop-filter: blur(8px);
    }
    .hb-dot { width: 10px; height: 10px; background: #4CAF82; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 0 3px rgba(76,175,130,.22); }
    .hb-info strong { display: block; font-size: 13px; font-weight: 600; color: var(--noir); }
    .hb-info span { font-size: 11px; color: var(--muted); font-weight: 300; }

    /* ===== SECTION À PROPOS ===== */
    .section-about {
      background: var(--white);
      padding: 100px 0;
    }

    .about-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: center;
    }

    .about-img-wrap {
      position: relative;
    }

    .about-img-main {
      width: 100%;
      height: 420px;
      object-fit: cover;
      border-radius: 24px;
      display: block;
    }

    /* Carte flottante sur la photo */
    .about-float-card {
      position: absolute;
      bottom: -24px;
      right: -24px;
      background: var(--white);
      border-radius: 16px;
      padding: 20px 24px;
      box-shadow: 0 8px 32px rgba(42,39,39,.12);
      border: 1px solid var(--border);
      min-width: 200px;
    }
    .afc-num {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      font-weight: 700;
      color: var(--bleu);
      line-height: 1;
    }
    .afc-label {
      font-size: 12px;
      color: var(--muted);
      font-weight: 300;
      margin-top: 4px;
    }

    .about-content { padding-left: 20px; }

    .about-points {
      list-style: none;
      margin: 28px 0 36px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .about-points li {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      font-size: 14px;
      color: var(--noir-l);
      line-height: 1.6;
    }
    .check-icon {
      width: 22px; height: 22px;
      background: var(--jaune);
      border-radius: 50%;
      flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      margin-top: 1px;
    }
    .check-icon svg { width: 11px; height: 11px; stroke: var(--bleu); fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }

    /* ===== SECTION SERVICES ===== */
    .section-services { padding: 110px 0 120px; background: var(--blanc); overflow: hidden; }
    .section-header { text-align: center; margin-bottom: 64px; }
    .section-header .section-sub { margin: 0 auto; }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    .svc-card {
      position: relative;
      border-radius: 24px;
      overflow: hidden;
      cursor: pointer;
      height: 480px;
      transform-style: preserve-3d;
      transition: box-shadow .4s ease;
      box-shadow: 0 8px 32px rgba(42,39,39,.10);
    }
    .svc-card:hover { box-shadow: 0 28px 64px rgba(42,39,39,.22); }

    .svc-bg {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .6s cubic-bezier(.22,1,.36,1);
      will-change: transform;
    }
    .svc-card:hover .svc-bg { transform: scale(1.08); }

    .svc-placeholder {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .svc-placeholder-emoji {
      font-size: 96px;
      filter: drop-shadow(0 8px 24px rgba(0,0,0,.2));
      transition: transform .5s cubic-bezier(.22,1,.36,1);
      will-change: transform;
    }
    .svc-card:hover .svc-placeholder-emoji { transform: scale(1.15) translateY(-8px); }
    .sp-menage { background: linear-gradient(160deg,#FBE8DF,#F2C4B4,#E89070); }
    .sp-garde  { background: linear-gradient(160deg,#D4EEE8,#B8DDD6,#7FCFC4); }
    .sp-accomp { background: linear-gradient(160deg,#E8E4F8,#D4CDF0,#A89EDA); }

    .svc-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(42,39,39,0) 30%, rgba(42,39,39,.55) 65%, rgba(42,39,39,.92) 100%);
      transition: opacity .35s ease;
    }

    .svc-color-overlay {
      position: absolute;
      inset: 0;
      opacity: 0;
      transition: opacity .4s ease;
    }
    .svc-card:hover .svc-color-overlay { opacity: .18; }
    .col-menage { background: var(--bleu); }
    .col-garde  { background: #1B9E7A; }
    .col-accomp { background: #7C5CC8; }

    .svc-body {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      padding: 28px;
      z-index: 2;
    }

    .svc-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 12px;
      border-radius: 50px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 10px;
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.3);
      color: #fff;
      background: rgba(255,255,255,.18);
      transition: background .3s;
    }
    .svc-card:hover .svc-tag { background: rgba(255,255,255,.28); }

    .svc-title {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color: #fff;
      line-height: 1.2;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .svc-title-icon {
      width: 36px; height: 36px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      backdrop-filter: blur(6px);
      transition: transform .35s cubic-bezier(.22,1,.36,1);
    }
    .svc-card:hover .svc-title-icon { transform: rotate(8deg) scale(1.12); }
    .svc-title-icon svg { width: 20px; height: 20px; }

    .svc-desc {
      font-size: 13px;
      font-weight: 300;
      color: rgba(255,255,255,.85);
      line-height: 1.65;
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transition: max-height .45s cubic-bezier(.22,1,.36,1), opacity .35s ease, margin .35s ease;
      margin-bottom: 0;
    }
    .svc-card:hover .svc-desc { max-height: 120px; opacity: 1; margin-bottom: 14px; }

    .svc-points {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transition: max-height .5s cubic-bezier(.22,1,.36,1), opacity .4s ease .05s;
    }
    .svc-card:hover .svc-points { max-height: 120px; opacity: 1; }

    .svc-point {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      color: rgba(255,255,255,.8);
    }
    .svc-point::before {
      content: '';
      width: 5px; height: 5px;
      border-radius: 50%;
      background: rgba(255,255,255,.7);
      flex-shrink: 0;
    }

    .svc-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 14px;
      opacity: 0;
      transform: translateY(8px);
      transition: opacity .35s ease .1s, transform .35s ease .1s;
    }
    .svc-card:hover .svc-footer { opacity: 1; transform: translateY(0); }

    .svc-missions { font-size: 12px; color: rgba(255,255,255,.7); font-weight: 300; }
    .svc-missions strong { color: #fff; font-weight: 600; }

    .svc-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,.2);
      border: 1px solid rgba(255,255,255,.35);
      color: #fff;
      font-size: 12px;
      font-weight: 500;
      padding: 7px 14px;
      border-radius: 50px;
      text-decoration: none;
      backdrop-filter: blur(6px);
      transition: background .2s;
      font-family: 'DM Sans', sans-serif;
      cursor: pointer;
    }
    .svc-btn:hover { background: rgba(255,255,255,.35); }

    .svc-glow {
      position: absolute;
      width: 200px; height: 200px;
      border-radius: 50%;
      pointer-events: none;
      opacity: 0;
      transition: opacity .3s ease;
      transform: translate(-50%, -50%);
      z-index: 1;
    }
    .svc-card:hover .svc-glow { opacity: 1; }
    .glow-menage { background: radial-gradient(circle, rgba(209,220,233,.35) 0%, transparent 70%); }
    .glow-garde  { background: radial-gradient(circle, rgba(27,158,122,.35) 0%, transparent 70%); }
    .glow-accomp { background: radial-gradient(circle, rgba(124,92,200,.35) 0%, transparent 70%); }

    /* ===== SECTION ÉTAPES ===== */
    .section-steps {
      padding: 100px 0;
      background: var(--noir);
      position: relative;
      overflow: hidden;
    }
    .section-steps::before {
      content: '';
      position: absolute;
      top: -100px; right: -100px;
      width: 400px; height: 400px;
      border-radius: 50%;
      background: var(--bleu);
      opacity: .06;
    }
    .section-steps::after {
      content: '';
      position: absolute;
      bottom: -80px; left: -80px;
      width: 300px; height: 300px;
      border-radius: 50%;
      background: #fff;
      opacity: .03;
    }

    .section-steps .section-label { background: rgba(209,220,233,.2); color: #F4A882; }
    .section-steps .section-title { color: #fff; }
    .section-steps .section-sub { color: rgba(255,255,255,.6); }

    .steps-header {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: center;
      margin-bottom: 64px;
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
      position: relative;
      z-index: 1;
    }

    .steps-grid::before {
      content: '';
      position: absolute;
      top: 28px;
      left: calc(16.67% + 24px);
      right: calc(16.67% + 24px);
      height: 1px;
      background: rgba(255,255,255,.15);
      z-index: 0;
    }

    .step-card {
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: 18px;
      padding: 28px 24px;
      position: relative;
      z-index: 1;
      transition: all .25s;
    }
    .step-card:hover { background: rgba(255,255,255,.1); transform: translateY(-4px); }

    .step-num {
      width: 44px; height: 44px;
      background: var(--bleu);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 18px;
    }

    .step-title { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px; line-height: 1.3; }
    .step-desc  { font-size: 13px; font-weight: 300; color: rgba(255,255,255,.6); line-height: 1.65; }

    /* ===== SECTION CHIFFRES ===== */
    .section-numbers { padding: 80px 0; background: var(--bleu); }

    .numbers-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0;
    }

    .number-item {
      text-align: center;
      padding: 20px;
      border-right: 1px solid rgba(255,255,255,.2);
    }
    .number-item:last-child { border-right: none; }

    .number-big {
      font-family: 'Playfair Display', serif;
      font-size: 52px;
      font-weight: 700;
      color: #fff;
      line-height: 1;
      display: block;
    }

    .number-label { font-size: 13px; color: rgba(255,255,255,.8); font-weight: 300; margin-top: 6px; display: block; }

    /* ===== SECTION POURQUOI ===== */
    .section-why { padding: 100px 0; background: var(--white); }

    .why-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      margin-top: 48px;
    }

    .why-card {
      background: var(--blanc);
      border-radius: 18px;
      padding: 28px;
      border: 1px solid var(--border);
      display: flex;
      gap: 18px;
      align-items: flex-start;
      transition: all .22s;
    }
    .why-card:hover { background: var(--white); box-shadow: 0 8px 28px rgba(42,39,39,.08); transform: translateY(-3px); }

    .why-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .why-icon svg { width: 24px; height: 24px; }
    .wi-salmon { background: var(--jaune); }
    .wi-green  { background: #ECFDF5; }
    .wi-blue   { background: #EFF6FF; }
    .wi-purple { background: #F5F3FF; }

    .why-content h3 { font-size: 16px; font-weight: 600; color: var(--noir); margin-bottom: 6px; }
    .why-content p  { font-size: 13px; font-weight: 300; color: var(--noir-l); line-height: 1.65; }

    /* ===== CTA SECTION ===== */
    .section-cta { padding: 80px 0; background: var(--rose); text-align: center; }

    .cta-box {
      background: var(--noir);
      border-radius: 28px;
      padding: 60px 48px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .cta-box::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 250px; height: 250px;
      border-radius: 50%;
      background: var(--bleu);
      opacity: .08;
    }

    .cta-box h2 { font-family: 'Playfair Display', serif; font-size: 40px; font-weight: 700; color: #fff; margin-bottom: 14px; position: relative; z-index: 1; }
    .cta-box p  { font-size: 15px; color: rgba(255,255,255,.65); font-weight: 300; margin-bottom: 32px; position: relative; z-index: 1; }

    .cta-btns { display: flex; align-items: center; justify-content: center; gap: 14px; position: relative; z-index: 1; }

    /* ===== FOOTER ===== */
    .site-footer { background: var(--noir); padding: 64px 0 0; border-top: 1px solid rgba(255,255,255,.06); }

    .footer-top {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 48px;
      padding-bottom: 48px;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .footer-brand img { height: 36px; margin-bottom: 18px; opacity: .9; display: block; }
    .footer-brand p   { font-size: 13px; color: rgba(255,255,255,.5); font-weight: 300; line-height: 1.7; margin-bottom: 20px; max-width: 280px; }

    .footer-contact-item { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: rgba(255,255,255,.5); margin-bottom: 8px; }
    .footer-contact-item svg { width: 14px; height: 14px; stroke: var(--bleu); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; }

    .footer-col h4 { font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,.4); margin-bottom: 18px; }
    .footer-col ul { list-style: none; }
    .footer-col ul li { margin-bottom: 10px; }
    .footer-col ul li a { font-size: 13px; color: rgba(255,255,255,.6); text-decoration: none; font-weight: 300; transition: color .2s; }
    .footer-col ul li a:hover { color: var(--bleu); }

    .footer-bottom { padding: 20px 0; display: flex; align-items: center; justify-content: space-between; }
    .footer-bottom p { font-size: 12px; color: rgba(255,255,255,.3); }
    .footer-bottom a { font-size: 12px; color: rgba(255,255,255,.3); text-decoration: none; }
    .footer-bottom a:hover { color: var(--bleu); }

    /* ===== MODALE CONNEXION ===== */
    .modal-overlay {
      display: none;
      position: fixed; inset: 0;
      background: rgba(42,39,39,.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.open { display: flex; animation: fadeOv .22s ease; }
    @keyframes fadeOv { from{opacity:0} to{opacity:1} }

    .modal {
      background: var(--white);
      border-radius: 24px;
      padding: 40px;
      width: 100%;
      max-width: 440px;
      position: relative;
      animation: slideD .28s ease;
      max-height: 92vh;
      overflow-y: auto;
    }
    @keyframes slideD { from{opacity:0;transform:translateY(-18px)} to{opacity:1;transform:translateY(0)} }

    .modal-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; background: var(--blanc); border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .2s; }
    .modal-close:hover { background: var(--rose); }
    .modal-close svg { width: 13px; height: 13px; stroke: var(--noir); fill: none; stroke-width: 2.5; stroke-linecap: round; }

    .modal-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: var(--bleu); display: block; margin-bottom: 8px; }
    .modal-title   { font-family: 'Playfair Display', serif; font-size: 30px; font-weight: 700; color: var(--noir); line-height: 1.2; margin-bottom: 5px; }
    .modal-sub     { font-size: 13px; color: var(--muted); font-weight: 300; margin-bottom: 24px; }

    .role-sw { display: grid; grid-template-columns: 1fr 1fr; background: var(--blanc); border-radius: 12px; padding: 3px; margin-bottom: 22px; border: 1px solid var(--border); gap: 3px; }
    .role-btn { padding: 9px 8px; border-radius: 9px; border: none; background: transparent; font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 500; color: var(--muted); cursor: pointer; transition: all .2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
    .role-btn.active { background: var(--white); color: var(--noir); box-shadow: 0 1px 5px rgba(42,39,39,.1); }
    .role-btn svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .fg { margin-bottom: 16px; }
    .flabel { display: block; font-size: 12px; font-weight: 500; color: var(--noir); margin-bottom: 6px; }
    .iw { position: relative; }
    .iico { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; stroke: var(--muted); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; pointer-events: none; transition: stroke .2s; }
    .finput { width: 100%; height: 48px; padding: 0 13px 0 42px; border: 1.5px solid var(--border); border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--noir); background: var(--blanc); outline: none; transition: all .2s; }
    .finput::placeholder { color: var(--muted); font-weight: 300; }
    .finput:focus { border-color: var(--bleu); background: var(--white); box-shadow: 0 0 0 3px rgba(209,220,233,.12); }
    .finput:focus ~ .iico { stroke: var(--bleu); }
    .pw-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
    .pw-link { font-size: 11px; color: var(--bleu); text-decoration: none; font-weight: 500; }
    .tpw { position: absolute; right: 13px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); line-height: 0; }
    .tpw svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .btn-submit { width: 100%; height: 50px; background: var(--bleu); color: #fff; border: none; border-radius: 50px; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 9px; transition: all .22s; margin-top: 6px; }
    .btn-submit:hover { background: var(--bleu-d); transform: translateY(-1px); box-shadow: 0 8px 22px rgba(209,220,233,.3); }
    .btn-submit svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; transition: transform .2s; }
    .btn-submit:hover svg { transform: translateX(4px); }

    .merr { padding: 11px 14px; border-radius: 10px; font-size: 12px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; }
    .merr svg { width: 14px; height: 14px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; }

    .demo-hint { margin-top: 18px; padding: 13px 15px; background: var(--blanc); border-radius: 12px; border: 1px solid var(--border); }
    .demo-hint p { font-size: 10.5px; color: var(--muted); font-style: italic; margin-bottom: 8px; }
    .demo-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
    .dl { font-size: 11px; color: var(--muted); }
    .dv { font-family: 'Courier New', monospace; font-size: 10.5px; color: var(--noir); background: var(--border); padding: 2px 7px; border-radius: 4px; }

    /* ===== ANIMATIONS SCROLL ===== */
    .reveal { opacity: 0; transform: translateY(28px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .1s; }
    .reveal-delay-2 { transition-delay: .2s; }
    .reveal-delay-3 { transition-delay: .3s; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
      .hero { grid-template-columns: 1fr; min-height: auto; }
      .hero-right { height: 320px; }
      .hero-left { padding: 40px 24px; }
      .about-grid, .steps-header { grid-template-columns: 1fr; gap: 40px; }
      .services-grid { grid-template-columns: 1fr; }
      .why-grid { grid-template-columns: 1fr; }
      .numbers-grid { grid-template-columns: repeat(2, 1fr); }
      .footer-top { grid-template-columns: 1fr 1fr; gap: 32px; }
      .footer-brand { grid-column: 1 / -1; }
      .container { padding: 0 20px; }
      .header-nav { display: none; }
      .steps-grid { grid-template-columns: 1fr; }
      .steps-grid::before { display: none; }
      .about-float-card { position: static; margin-top: 20px; }
      .cta-btns { flex-direction: column; }
      .hero-stats { flex-wrap: wrap; }
      .feature-cards { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

<!-- HEADER FIXE -->
<header class="site-header" id="siteHeader">
  <div class="header-inner">
    <a href="#" class="header-logo">
      <img src="<?= BASE_URL ?>assets/images/monlogo.png" alt="FamiCare">
    </a>
    <nav class="header-nav">
      <a href="#about">À propos</a>
      <a href="#services">Nos services</a>
      <a href="#steps">Comment ça marche</a>
      <a href="#why">Pourquoi nous</a>
    </nav>
    <div class="header-right">
      <button class="btn btn-primary" onclick="openModal()" style="padding:9px 22px;font-size:13px;">Connexion</button>
    </div>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="hero-left">
    <div class="badge-intern">Plateforme interne FamiCare</div>
    <h1 class="hero-title">Évaluez vos<br>intervenantes<br>avec <em>précision</em></h1>
    <p class="hero-sub">Une plateforme dédiée à l'évaluation, la formation et le suivi de la montée en compétences de vos équipes à domicile.</p>
    <div class="hero-cta-group">
      <button class="btn btn-primary" onclick="openModal()">
        Accéder à mon espace
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
      <a href="#about" class="btn btn-outline">Découvrir →</a>
    </div>
    <div class="hero-stats">
      <div class="hero-stat"><strong>1 200+</strong><span>Intervenantes</span></div>
      <div class="hero-stat"><strong>98%</strong><span>Satisfaction</span></div>
      <div class="hero-stat"><strong>3</strong><span>Métiers couverts</span></div>
    </div>
    <div class="feature-cards">
      <div class="feature-card">
        <div class="fi fi-blue">
          <svg viewBox="0 0 24 24" fill="none"><rect x="5" y="4" width="14" height="17" rx="2.5" fill="#DBEAFE" stroke="#3B82F6" stroke-width="1.5"/><rect x="9" y="2" width="6" height="4" rx="1.5" fill="#93C5FD" stroke="#3B82F6" stroke-width="1.5"/><line x1="8" y1="11" x2="16" y2="11" stroke="#3B82F6" stroke-width="1.5" stroke-linecap="round"/><line x1="8" y1="14" x2="14" y2="14" stroke="#93C5FD" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div class="ft"><div class="fl">Tests structurés</div><div class="fd">QCM par métier</div></div>
      </div>
      <div class="feature-card">
        <div class="fi fi-green">
          <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="10" r="6" fill="#D1FAE5" stroke="#10B981" stroke-width="1.5"/><polyline points="9.5 10 11.2 12 14.5 8.5" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 15.5L7 21l3.5-1.5L12 21" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 15.5L17 21l-3.5-1.5L12 21" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="ft"><div class="fl">Mention immédiate</div><div class="fd">Score instantané</div></div>
      </div>
      <div class="feature-card">
        <div class="fi fi-purple">
          <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="13" width="4" height="8" rx="1.5" fill="#EDE9FE" stroke="#8B5CF6" stroke-width="1.5"/><rect x="10" y="8" width="4" height="13" rx="1.5" fill="#DDD6FE" stroke="#8B5CF6" stroke-width="1.5"/><rect x="17" y="3" width="4" height="18" rx="1.5" fill="#C4B5FD" stroke="#8B5CF6" stroke-width="1.5"/></svg>
        </div>
        <div class="ft"><div class="fl">Suivi & KPIs</div><div class="fd">Tableau de bord</div></div>
      </div>
    </div>
  </div>
  <div class="hero-right">
    <img src="<?= BASE_URL ?>assets/images/intervenante.jpg" alt="Intervenante FamiCare souriante">
    <div class="hero-badge">
      <div class="hb-dot"></div>
      <div class="hb-info"><strong>Plateforme active</strong><span>Évaluations en ligne</span></div>
    </div>
  </div>
</section>

<!-- À PROPOS -->
<section class="section-about" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="about-img-wrap reveal">
        <img src="<?= BASE_URL ?>assets/images/intervenante.jpg" alt="Équipe FamiCare" class="about-img-main">
        <div class="about-float-card">
          <div class="afc-num">98%</div>
          <div class="afc-label">de nos intervenantes satisfaites · Île-de-France & Lyon</div>
        </div>
      </div>
      <div class="about-content reveal reveal-delay-1">
        <span class="section-label">À propos</span>
        <h2 class="section-title">Qui est<br>FamiCare ?</h2>
        <p class="section-sub">FamiCare est une agence d'aide à domicile basée à Paris, spécialisée dans le ménage, le repassage, la garde d'enfants et l'accompagnement. Notre mission : vous offrir des intervenantes formées, évaluées et engagées pour le bien-être de votre famille.</p>
        <ul class="about-points">
          <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Intervenantes sélectionnées, formées et régulièrement évaluées sur leurs compétences métier</span></li>
          <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Postes en CDD ou CDI à temps plein ou partiel selon vos disponibilités</span></li>
          <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Formation gratuite de 15 jours dès l'intégration pour garantir la qualité de service</span></li>
          <li><div class="check-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><span>Présents en Île-de-France et à Lyon avec plus de 1 200 intervenantes actives</span></li>
        </ul>
        <button class="btn btn-primary" onclick="openModal()">Accéder à la plateforme →</button>
      </div>
    </div>
  </div>
</section>

<!-- NOS SERVICES -->
<section class="section-services" id="services">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Nos services</span>
      <h2 class="section-title">3 métiers,<br>une excellence commune</h2>
      <p class="section-sub">Survole chaque carte pour découvrir les détails du service. Nos intervenantes sont évaluées régulièrement sur chaque compétence.</p>
    </div>
    <div class="services-grid">

      <div class="svc-card reveal" id="card-menage">
        <img class="svc-bg" src="<?= BASE_URL ?>assets/images/menage.jpg" alt="Ménage">
        <div class="svc-overlay"></div>
        <div class="svc-color-overlay col-menage"></div>
        <div class="svc-glow glow-menage"></div>
        <div class="svc-body">
          <div class="svc-tag">● MÉNAGE</div>
          <h3 class="svc-title">
            <div class="svc-title-icon" style="background:rgba(209,220,233,.3)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21l7-7m0 0l4-4m-4 4l-1.5-1.5M10 14l4-4m0 0l3.5-3.5a2.121 2.121 0 013 3L17 14"/><path d="M14 10l-4 4"/></svg>
            </div>
            Ménage &amp; Repassage
          </h3>
          <p class="svc-desc">Entretien complet du domicile, nettoyage des surfaces et repassage professionnel. Nos intervenantes sont évaluées sur les techniques et produits adaptés à chaque surface.</p>
          <ul class="svc-points">
            <li class="svc-point">Nettoyage sols, surfaces, sanitaires et cuisine</li>
            <li class="svc-point">Repassage du linge délicat avec soin</li>
            <li class="svc-point">Produits adaptés à chaque matière</li>
          </ul>
          <div class="svc-footer">
            <div class="svc-missions"><strong>367</strong> missions disponibles</div>
            <button class="svc-btn" onclick="openModal()">Accéder →</button>
          </div>
        </div>
      </div>

      <div class="svc-card reveal reveal-delay-1" id="card-garde">
        <img class="svc-bg" src="<?= BASE_URL ?>assets/images/garde.jpg" alt="Garde d'enfants">
        <div class="svc-overlay"></div>
        <div class="svc-color-overlay col-garde"></div>
        <div class="svc-glow glow-garde"></div>
        <div class="svc-body">
          <div class="svc-tag">● GARDE D'ENFANTS</div>
          <h3 class="svc-title">
            <div class="svc-title-icon" style="background:rgba(27,158,122,.35)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            Garde d'enfants
          </h3>
          <p class="svc-desc">Garde à domicile bienveillante, aide aux devoirs et activités éducatives. Les intervenantes sont testées sur la sécurité, l'éveil et leur capacité à créer un environnement rassurant.</p>
          <ul class="svc-points">
            <li class="svc-point">Aide aux devoirs et soutien scolaire</li>
            <li class="svc-point">Activités créatives adaptées à l'âge</li>
            <li class="svc-point">Sécurité et protocoles d'urgence maîtrisés</li>
          </ul>
          <div class="svc-footer">
            <div class="svc-missions"><strong>604</strong> missions disponibles</div>
            <button class="svc-btn" onclick="openModal()">Accéder →</button>
          </div>
        </div>
      </div>

      <div class="svc-card reveal reveal-delay-2" id="card-accomp">
        <img class="svc-bg" src="<?= BASE_URL ?>assets/images/accomp.avif" alt="Maintien à domicile">
        <div class="svc-overlay"></div>
        <div class="svc-color-overlay col-accomp"></div>
        <div class="svc-glow glow-accomp"></div>
        <div class="svc-body">
          <div class="svc-tag">● ACCOMPAGNEMENT</div>
          <h3 class="svc-title">
            <div class="svc-title-icon" style="background:rgba(124,92,200,.35)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </div>
            Maintien à domicile
          </h3>
          <p class="svc-desc">Assistance aux personnes âgées ou dépendantes : courses, rendez-vous médicaux, compagnie. Évaluation sur l'empathie, la communication et les gestes de premiers secours.</p>
          <ul class="svc-points">
            <li class="svc-point">Courses et accompagnements médicaux</li>
            <li class="svc-point">Aide à la prise de médicaments</li>
            <li class="svc-point">Présence et lutte contre l'isolement</li>
          </ul>
          <div class="svc-footer">
            <div class="svc-missions"><strong>178</strong> missions disponibles</div>
            <button class="svc-btn" onclick="openModal()">Accéder →</button>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- CHIFFRES CLÉS -->
<section class="section-numbers">
  <div class="container">
    <div class="numbers-grid">
      <div class="number-item reveal"><strong class="number-big">1 200+</strong><span class="number-label">Intervenantes actives</span></div>
      <div class="number-item reveal reveal-delay-1"><strong class="number-big">98%</strong><span class="number-label">Taux de satisfaction</span></div>
      <div class="number-item reveal reveal-delay-2"><strong class="number-big">15j</strong><span class="number-label">Formation offerte</span></div>
      <div class="number-item reveal reveal-delay-3"><strong class="number-big">2</strong><span class="number-label">Régions couvertes</span></div>
    </div>
  </div>
</section>

<!-- ÉTAPES -->
<section class="section-steps" id="steps">
  <div class="container">
    <div class="steps-header">
      <div class="reveal">
        <span class="section-label">Comment ça marche</span>
        <h2 class="section-title">Votre parcours<br>d'évaluation</h2>
      </div>
      <div class="reveal reveal-delay-1">
        <p style="font-size:15px;font-weight:300;color:rgba(255,255,255,.6);line-height:1.7;">Chaque intervenante passe par un processus d'évaluation structuré, conçu pour mesurer ses compétences métier et garantir la qualité du service rendu aux familles.</p>
      </div>
    </div>
    <div class="steps-grid">
      <div class="step-card reveal"><div class="step-num">1</div><h3 class="step-title">Connexion sécurisée</h3><p class="step-desc">L'intervenante accède à son espace personnel avec ses identifiants fournis par FamiCare. Chaque compte est lié à sa spécialité métier.</p></div>
      <div class="step-card reveal reveal-delay-1"><div class="step-num">2</div><h3 class="step-title">Passage du test QCM</h3><p class="step-desc">Elle répond à des questions illustrées sur ses gestes professionnels, les produits, la sécurité et les situations du quotidien.</p></div>
      <div class="step-card reveal reveal-delay-2"><div class="step-num">3</div><h3 class="step-title">Résultat & suivi admin</h3><p class="step-desc">Le score est calculé automatiquement. L'admin reçoit une alerte en cas de score faible et suit l'évolution de chaque intervenante.</p></div>
    </div>
  </div>
</section>

<!-- POURQUOI -->
<section class="section-why" id="why">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Pourquoi nous rejoindre</span>
      <h2 class="section-title">Les avantages FamiCare</h2>
      <p class="section-sub">Travailler chez FamiCare, c'est bénéficier d'un accompagnement complet, de missions régulières et d'une vraie reconnaissance de vos compétences.</p>
    </div>
    <div class="why-grid">
      <div class="why-card reveal"><div class="why-icon wi-salmon"><svg viewBox="0 0 24 24" stroke="var(--bleu)" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div class="why-content"><h3>Contrat stable CDD / CDI</h3><p>Postes à temps plein ou partiel selon vos disponibilités. Un emploi stable avec une protection sociale complète.</p></div></div>
      <div class="why-card reveal reveal-delay-1"><div class="why-icon wi-green"><svg viewBox="0 0 24 24" stroke="#10B981" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div><div class="why-content"><h3>Formation gratuite de 15 jours</h3><p>Dès votre intégration, suivez une formation d'aide à domicile entièrement prise en charge par FamiCare.</p></div></div>
      <div class="why-card reveal reveal-delay-2"><div class="why-icon wi-blue"><svg viewBox="0 0 24 24" stroke="#3B82F6" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="why-content"><h3>Missions rapides & régulières</h3><p>Recevez vos premières missions de ménage, repassage ou garde d'enfants en quelques jours seulement.</p></div></div>
      <div class="why-card reveal reveal-delay-3"><div class="why-icon wi-purple"><svg viewBox="0 0 24 24" stroke="#8B5CF6" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div><div class="why-content"><h3>Suivi & évolution personnalisée</h3><p>Votre progression est suivie grâce à cette plateforme d'évaluation. Vos compétences sont reconnues et valorisées.</p></div></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section-cta">
  <div class="container">
    <div class="cta-box reveal">
      <h2>Prête à accéder<br>à votre espace ?</h2>
      <p>Connectez-vous pour passer vos évaluations et suivre votre progression au sein de FamiCare.</p>
      <div class="cta-btns">
        <button class="btn btn-primary" onclick="openModal()" style="font-size:15px;padding:14px 32px;">Accéder à mon espace →</button>
        <a href="https://famicare.fr" target="_blank" class="btn btn-white">Site FamiCare ↗</a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <img src="<?= BASE_URL ?>assets/images/monlogo.png" alt="FamiCare">
        <p>Plateforme interne d'évaluation des intervenantes FamiCare. Développée dans le cadre d'un stage L3 Informatique.</p>
        <div class="footer-contact-item"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg><span>73 Rue de Lourmel, 75015 Paris</span></div>
        <div class="footer-contact-item"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>contact@famicare.fr</span></div>
        <div class="footer-contact-item"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.1 2.18 2 2 0 012.08.02h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/></svg><span>01 84 60 48 38</span></div>
      </div>
      <div class="footer-col"><h4>Application</h4><ul><li><a href="#about">À propos</a></li><li><a href="#services">Nos services</a></li><li><a href="#steps">Comment ça marche</a></li><li><a href="#why">Pourquoi nous rejoindre</a></li></ul></div>
      <div class="footer-col"><h4>Légal</h4><ul><li><a href="https://famicare.fr/mentions-legales/" target="_blank">Mentions légales</a></li><li><a href="https://famicare.fr/politique-de-confidentialite/" target="_blank">Politique de confidentialité</a></li></ul></div>
      <div class="footer-col"><h4>FamiCare</h4><ul><li><a href="https://famicare.fr/" target="_blank">Site officiel</a></li><li><a href="https://famicare.fr/a-propos/" target="_blank">À propos</a></li><li><a href="https://famicare.fr/rejoignez-nous/" target="_blank">Rejoindre l'équipe</a></li><li><a href="https://famicare.fr/contact/" target="_blank">Contact</a></li></ul></div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> FamiCare — Tous droits réservés · Plateforme développée en stage L3</p>
      <div style="display:flex;gap:20px;"><a href="https://famicare.fr/mentions-legales/" target="_blank">Mentions légales</a><a href="https://famicare.fr/politique-de-confidentialite/" target="_blank">Confidentialité</a></div>
    </div>
  </div>
</footer>

<!-- MODALE CONNEXION -->
<div class="modal-overlay" id="modalOverlay" onclick="closeOnOverlay(event)">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()" aria-label="Fermer">
      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <span class="modal-eyebrow">Accès sécurisé</span>
    <h2 class="modal-title">Bienvenue sur<br>votre espace</h2>
    <p class="modal-sub">Connectez-vous pour accéder à vos évaluations</p>
    <div class="role-sw">
      <button type="button" class="role-btn active" onclick="setRole('intervenante',this)">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Intervenante
      </button>
      <button type="button" class="role-btn" onclick="setRole('admin',this)">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Administrateur
      </button>
    </div>

    <?php if ($erreur): ?>
    <div class="merr">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($erreur) ?>
    </div>
    <?php endif; ?>

    <!-- ✅ MODIFICATION CLÉ : action pointe vers login.php -->
    <form method="POST" action="<?= BASE_URL ?>login.php">
      <input type="hidden" name="role" id="roleInput" value="intervenante">
      <div class="fg">
        <label class="flabel" for="email">Adresse email</label>
        <div class="iw">
          <input type="email" id="email" name="email" class="finput" placeholder="prenom@famicare.fr" autocomplete="email" required>
          <svg class="iico" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
      </div>
      <div class="fg">
        <div class="pw-row">
          <label class="flabel" for="mdp" style="margin:0">Mot de passe</label>
          <a href="#" class="pw-link">Oublié ?</a>
        </div>
        <div class="iw">
          <input type="password" id="mdp" name="mot_de_passe" class="finput" placeholder="••••••••" autocomplete="current-password" required>
          <svg class="iico" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <button type="button" class="tpw" onclick="togglePw()" aria-label="Voir">
            <svg id="eyeIco" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-submit">
        Se connecter
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </form>

    <div class="demo-hint">
      <p>Comptes de test — mot de passe : <strong>password</strong></p>
      <div class="demo-row"><span class="dl">Admin :</span><span class="dv">admin@famicare.fr</span></div>
      <div class="demo-row" style="margin-bottom:0"><span class="dl">Intervenante :</span><span class="dv">marie.dupont@email.fr</span></div>
    </div>
  </div>
</div>

<script>
  window.addEventListener('scroll', function() {
    document.getElementById('siteHeader').classList.toggle('scrolled', window.scrollY > 20);
  });

  function openModal()  { document.getElementById('modalOverlay').classList.add('open'); document.body.style.overflow='hidden'; }
  function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); document.body.style.overflow=''; }
  function closeOnOverlay(e) { if(e.target===document.getElementById('modalOverlay')) closeModal(); }

  function setRole(role, btn) {
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }

  function togglePw() {
    const inp = document.getElementById('mdp');
    const ico = document.getElementById('eyeIco');
    if (inp.type === 'password') {
      inp.type = 'text';
      ico.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
      inp.type = 'password';
      ico.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
  }

  document.querySelectorAll('.svc-card').forEach(function(card) {
    var glow = card.querySelector('.svc-glow');
    if (!glow) return;
    card.addEventListener('mousemove', function(e) {
      var rect = card.getBoundingClientRect();
      var x = e.clientX - rect.left;
      var y = e.clientY - rect.top;
      glow.style.left = x + 'px';
      glow.style.top  = y + 'px';
      var cx = rect.width / 2, cy = rect.height / 2;
      var rotX = ((y - cy) / cy) * -6;
      var rotY = ((x - cx) / cx) *  6;
      card.style.transform = 'perspective(800px) rotateX(' + rotX + 'deg) rotateY(' + rotY + 'deg) scale(1.02)';
    });
    card.addEventListener('mouseleave', function() {
      card.style.transform = 'perspective(800px) rotateX(0) rotateY(0) scale(1)';
      card.style.transition = 'transform .5s cubic-bezier(.22,1,.36,1), box-shadow .4s ease';
    });
    card.addEventListener('mouseenter', function() {
      card.style.transition = 'transform .15s ease, box-shadow .4s ease';
    });
  });

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) entry.target.classList.add('visible');
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal').forEach(function(el) { observer.observe(el); });

  <?php if ($erreur): ?>window.addEventListener('load', openModal);<?php endif; ?>
</script>

</body>
</html>