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

*À compléter après exécution sur les données réelles : tableau de VaR comparée (historique / paramétrique / Monte Carlo), poids optimaux, résultat du backtest, et 2 ou 3 graphiques de `outputs/figures/`.*

## Limites et pistes d'amélioration

- Rendements supposés i.i.d. ; paramètres estimés sur le passé.
- Monte Carlo gaussien : sous-estime les queues épaisses. Piste : loi de Student, GARCH, Filtered Historical Simulation.
- Pas de coûts de transaction, actifs en USD uniquement (pas de risque de change).

## Outils

Python, pandas, NumPy, SciPy, Matplotlib, Jupyter.
