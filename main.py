"""Pipeline complet : données -> statistiques -> VaR (3 méthodes) -> frontière efficiente -> backtest."""
import argparse

import numpy as np
import pandas as pd

from src import plots, risk
from src.data import TICKERS, download_prices, simulate_prices

ALPHA = 0.99
RF = 0.02


def main(demo: bool = False):
    prices = simulate_prices() if demo else download_prices()
    if demo:
        print("[MODE DÉMO] Données simulées, résultats non représentatifs du marché.\n")
    returns = risk.log_returns(prices)
    w = np.ones(len(prices.columns)) / len(prices.columns)  # portefeuille équipondéré
    r_p = risk.portfolio_returns(returns, w)

    print("=== Statistiques par actif ===")
    print(risk.annual_stats(returns).round(4), "\n")

    # VaR
    var_h = risk.var_historical(r_p, ALPHA)
    var_p = risk.var_parametric(r_p, ALPHA)
    sims = risk.simulate_portfolio(returns, w, horizon=1, n_sims=10_000)
    var_mc = -np.quantile(sims, 1 - ALPHA)
    es = risk.es_historical(r_p, ALPHA)
    var_10d = risk.var_monte_carlo(returns, w, ALPHA, horizon=10)

    summary = pd.Series({
        "Rendement annuel": r_p.mean() * risk.TRADING_DAYS,
        "Volatilité annuelle": r_p.std() * np.sqrt(risk.TRADING_DAYS),
        "Sharpe": risk.sharpe_ratio(r_p, RF),
        "Max drawdown": risk.max_drawdown(r_p),
        f"VaR {ALPHA:.0%} 1j historique": var_h,
        f"VaR {ALPHA:.0%} 1j paramétrique": var_p,
        f"VaR {ALPHA:.0%} 1j Monte Carlo": var_mc,
        f"ES {ALPHA:.0%} 1j historique": es,
        f"VaR {ALPHA:.0%} 10j Monte Carlo": var_10d,
    })
    print("=== Portefeuille équipondéré ===")
    print(summary.round(4), "\n")
    summary.to_csv("outputs/resume_portefeuille.csv", header=["valeur"])

    # Frontière efficiente
    frontier, w_min, w_sharpe = risk.efficient_frontier(returns, rf=RF)
    mu = returns.mean() * risk.TRADING_DAYS
    vol = returns.std() * np.sqrt(risk.TRADING_DAYS)
    pt = lambda ww: (np.sqrt(ww @ (returns.cov().values * 252) @ ww), ww @ mu.values)
    print("=== Poids optimaux ===")
    print(pd.DataFrame({"Min variance": w_min, "Sharpe max": w_sharpe}).round(3), "\n")

    # Backtest
    bt = risk.backtest_var(r_p, ALPHA)
    n_exc, n_obs = int(bt["exception"].sum()), len(bt)
    pval = risk.kupiec_test(n_exc, n_obs, ALPHA)
    print(f"=== Backtest VaR {ALPHA:.0%} === exceptions: {n_exc}/{n_obs} "
          f"(attendu ~{n_obs*(1-ALPHA):.1f}) | p-value Kupiec: {pval:.3f}")

    # Graphiques
    plots.plot_prices(prices)
    plots.plot_corr(returns)
    plots.plot_frontier(frontier, vol, mu, pt(w_min.values), pt(w_sharpe.values), pt(w))
    plots.plot_var_distribution(r_p, sims, var_h, var_p, var_mc, ALPHA)
    plots.plot_backtest(bt, ALPHA)
    print("\nGraphiques enregistrés dans outputs/figures/")


if __name__ == "__main__":
    ap = argparse.ArgumentParser()
    ap.add_argument("--demo", action="store_true", help="utilise des données simulées (hors-ligne)")
    main(ap.parse_args().demo)
