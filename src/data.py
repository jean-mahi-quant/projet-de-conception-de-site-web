"""Chargement des prix : Yahoo Finance (yfinance) avec cache CSV, ou données simulées pour tests hors-ligne."""
from pathlib import Path

import numpy as np
import pandas as pd

TICKERS = ["AAPL", "MSFT", "JPM", "XOM", "SPY", "TLT", "GLD"]
CACHE = Path(__file__).resolve().parent.parent / "data" / "prices.csv"


def download_prices(tickers=TICKERS, start="2020-01-01", end=None, use_cache=True) -> pd.DataFrame:
    """Prix de clôture ajustés (dividendes/splits inclus)."""
    if use_cache and CACHE.exists():
        return pd.read_csv(CACHE, index_col=0, parse_dates=True)
    import yfinance as yf

    px = yf.download(tickers, start=start, end=end, auto_adjust=True, progress=False)["Close"]
    px = px[tickers].dropna(how="any")
    CACHE.parent.mkdir(exist_ok=True)
    px.to_csv(CACHE)
    return px


def simulate_prices(tickers=TICKERS, n_days=1000, seed=42) -> pd.DataFrame:
    """Prix SIMULÉS (corrélés) : uniquement pour tester le code sans connexion. Ne pas présenter comme des données réelles."""
    rng = np.random.default_rng(seed)
    k = len(tickers)
    vols = np.linspace(0.12, 0.30, k) / np.sqrt(252)
    mus = np.linspace(0.03, 0.10, k) / 252
    A = rng.normal(size=(k, k))
    corr = A @ A.T
    d = np.sqrt(np.diag(corr))
    corr = corr / np.outer(d, d)
    cov = corr * np.outer(vols, vols)
    rets = rng.multivariate_normal(mus, cov, size=n_days)
    idx = pd.bdate_range(end=pd.Timestamp.today().normalize(), periods=n_days)
    return pd.DataFrame(100 * np.exp(np.cumsum(rets, axis=0)), index=idx, columns=tickers)
