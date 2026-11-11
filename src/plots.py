"""Graphiques (enregistrés dans outputs/figures/)."""
from pathlib import Path

import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt

FIG = Path(__file__).resolve().parent.parent / "outputs" / "figures"
FIG.mkdir(parents=True, exist_ok=True)


def _save(fig, name):
    fig.tight_layout()
    fig.savefig(FIG / name, dpi=150)
    return fig


def plot_prices(prices):
    fig, ax = plt.subplots(figsize=(9, 4.5))
    (prices / prices.iloc[0] * 100).plot(ax=ax)
    ax.set(title="Prix normalisés (base 100)", ylabel="Base 100")
    return _save(fig, "01_prix_normalises.png")


def plot_corr(returns):
    fig, ax = plt.subplots(figsize=(6, 5))
    c = returns.corr()
    im = ax.imshow(c, cmap="RdYlGn", vmin=-1, vmax=1)
    ax.set_xticks(range(len(c)), c.columns, rotation=45)
    ax.set_yticks(range(len(c)), c.columns)
    for i in range(len(c)):
        for j in range(len(c)):
            ax.text(j, i, f"{c.iloc[i, j]:.2f}", ha="center", va="center", fontsize=8)
    fig.colorbar(im)
    ax.set_title("Matrice de corrélation")
    return _save(fig, "02_correlation.png")


def plot_frontier(frontier, assets_vol, assets_ret, minvol_pt, maxsharpe_pt, port_pt):
    fig, ax = plt.subplots(figsize=(8, 5))
    ax.plot(frontier[:, 0], frontier[:, 1], "b-", label="Frontière efficiente")
    ax.scatter(assets_vol, assets_ret, c="grey", label="Actifs")
    for name, v, r in zip(assets_vol.index, assets_vol, assets_ret):
        ax.annotate(name, (v, r), fontsize=8)
    ax.scatter(*minvol_pt, c="green", marker="*", s=200, label="Variance minimale")
    ax.scatter(*maxsharpe_pt, c="red", marker="*", s=200, label="Sharpe maximal")
    ax.scatter(*port_pt, c="orange", marker="D", s=80, label="Portefeuille équipondéré")
    ax.set(xlabel="Volatilité annuelle", ylabel="Rendement annuel", title="Frontière efficiente (long-only)")
    ax.legend()
    return _save(fig, "03_frontiere_efficiente.png")


def plot_var_distribution(r, sims, var_h, var_p, var_mc, alpha=0.99):
    fig, ax = plt.subplots(figsize=(9, 4.5))
    ax.hist(r, bins=60, density=True, alpha=0.5, label="Rendements historiques")
    ax.hist(sims, bins=60, density=True, alpha=0.4, label="Monte Carlo (10 000)")
    for v, c, n in [(var_h, "red", "historique"), (var_p, "green", "paramétrique"), (var_mc, "purple", "Monte Carlo")]:
        ax.axvline(-v, color=c, ls="--", label=f"VaR {alpha:.0%} {n} : {v:.2%}")
    ax.set(title="Distribution des rendements journaliers et VaR", xlabel="Rendement")
    ax.legend(fontsize=8)
    return _save(fig, "04_distribution_var.png")


def plot_backtest(bt, alpha=0.99):
    fig, ax = plt.subplots(figsize=(10, 4.5))
    ax.plot(bt.index, bt["rendement"], lw=0.6, label="Rendement")
    ax.plot(bt.index, -bt["VaR"], "r", lw=1, label=f"-VaR {alpha:.0%} glissante")
    exc = bt[bt["exception"]]
    ax.scatter(exc.index, exc["rendement"], c="black", s=20, zorder=3, label=f"Exceptions ({len(exc)})")
    ax.set(title="Backtesting de la VaR historique", ylabel="Rendement journalier")
    ax.legend()
    return _save(fig, "05_backtest_var.png")
