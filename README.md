# Modélisation de portefeuille et analyse de risque

Projet Python d'analyse d'un portefeuille multi-actifs : performance, risque (VaR, Expected Shortfall), optimisation (frontière efficiente) et validation par backtesting.

## Contenu

| Étape | Méthode |
|---|---|
| Données | Prix ajustés Yahoo Finance (`yfinance`), 7 actifs : actions, indice, obligations, or |
| Performance | Rendement et volatilité annualisés, ratio de Sharpe, max drawdown |
| VaR / ES | Historique, paramétrique (normale), Monte Carlo (rendements corrélés, Cholesky) |
| Optimisation | Frontière efficiente long-only, portefeuilles variance minimale et Sharpe maximal (`scipy.optimize`) |
| Validation | Backtesting de la VaR en fenêtre glissante, test de Kupiec |

## Structure

```
├── main.py                  # pipeline complet (python main.py)
├── notebooks/
│   └── analyse_risque_portefeuille.ipynb
├── src/
│   ├── data.py              # téléchargement + cache des prix
│   ├── risk.py              # métriques de risque et optimisation
│   └── plots.py             # graphiques
├── outputs/figures/         # graphiques générés
├── requirements.txt
└── README.md
```

## Installation et exécution

```bash
git clone https://github.com/<ton-utilisateur>/portfolio-risk-analysis.git
cd portfolio-risk-analysis
python -m venv .venv && source .venv/bin/activate   # Windows : .venv\Scripts\activate
pip install -r requirements.txt

python main.py                  # télécharge les données, calcule tout, génère les graphiques
jupyter notebook notebooks/     # version commentée pas à pas
```

`python main.py --demo` exécute le pipeline sur des données simulées (sans connexion) : utile pour tester, mais les résultats ne sont pas représentatifs du marché.

## Résultats

## Résultats

Période : janvier 2020 à novembre 2026, 7 actifs (AAPL, MSFT, JPM, XOM, SPY, TLT, GLD), portefeuille équipondéré.

| Indicateur | Valeur |
|---|---|
| Rendement annuel | 13,8 % |
| Volatilité annuelle | 16,0 % |
| Ratio de Sharpe (rf = 2 %) | 0,74 |
| Max drawdown | -27,0 % |
| VaR 99 % 1 jour, historique | 2,75 % |
| VaR 99 % 1 jour, paramétrique | 2,29 % |
| VaR 99 % 1 jour, Monte Carlo | 2,24 % |
| Expected Shortfall 99 % 1 jour | 4,38 % |
| VaR 99 % 10 jours, Monte Carlo | 6,47 % |

**Lecture.** Les méthodes gaussiennes (paramétrique, Monte Carlo) donnent une VaR inférieure d'environ 20 % à la VaR historique, et l'Expected Shortfall vaut 1,6 fois la VaR : les queues de distribution sont plus épaisses que la loi normale.

**Backtest.** Sur 1 474 jours, la VaR historique glissante à 99 % est dépassée 24 fois pour environ 15 attendues (p-value de Kupiec : 0,026). Le modèle sous-estime le risque, notamment dans les périodes où les pertes se regroupent. Pistes : GARCH, Filtered Historical Simulation, loi de Student.

**Optimisation.** Variance minimale : TLT 47 %, GLD 21 %, SPY 16 %. Sharpe maximal : GLD 51 %, AAPL 26 %. Ces poids sont calculés sur le passé et très concentrés, ils ne constituent pas une recommandation d'investissement.

![Distribution et VaR](outputs/figures/04_distribution_var.png)
![Frontière efficiente](outputs/figures/03_frontiere_efficiente.png)
![Backtest de la VaR](outputs/figures/05_backtest_var.png)

## Limites et pistes d'amélioration

- Rendements supposés i.i.d. ; paramètres estimés sur le passé.
- Monte Carlo gaussien : sous-estime les queues épaisses. Piste : loi de Student, GARCH, Filtered Historical Simulation.
- Pas de coûts de transaction, actifs en USD uniquement (pas de risque de change).

## Outils

Python, pandas, NumPy, SciPy, Matplotlib, Jupyter.
