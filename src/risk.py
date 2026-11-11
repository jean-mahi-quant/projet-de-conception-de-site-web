"""Métriques de risque et de performance d'un portefeuille."""
import numpy as np
import pandas as pd
from scipy import stats
from scipy.optimize import minimize

TRADING_DAYS = 252


# ---------- Rendements & statistiques ----------
def log_returns(prices: pd.DataFrame) -> pd.DataFrame:
    return np.log(prices / prices.shift(1)).dropna()


def portfolio_returns(returns: pd.DataFrame, weights) -> pd.Series:
    return returns @ np.asarray(weights)


def annual_stats(returns: pd.DataFrame) -> pd.DataFrame:
    return pd.DataFrame({
        "Rendement annuel": returns.mean() * TRADING_DAYS,
        "Volatilité annuelle": returns.std() * np.sqrt(TRADING_DAYS),
    })


def sharpe_ratio(r: pd.Series, rf: float = 0.02) -> float:
    """Sharpe annualisé ; rf = taux sans risque annuel."""
    excess = r.mean() * TRADING_DAYS - rf
    return excess / (r.std() * np.sqrt(TRADING_DAYS))


def max_drawdown(r: pd.Series) -> float:
    cum = np.exp(r.cumsum())
    return (cum / cum.cummax() - 1).min()


# ---------- VaR / Expected Shortfall (perte positive, en % du portefeuille) ----------
def var_historical(r: pd.Series, alpha: float = 0.99) -> float:
    return -np.quantile(r, 1 - alpha)


def var_parametric(r: pd.Series, alpha: float = 0.99) -> float:
    return -(r.mean() + stats.norm.ppf(1 - alpha) * r.std())


def es_historical(r: pd.Series, alpha: float = 0.99) -> float:
    q = np.quantile(r, 1 - alpha)
    return -r[r <= q].mean()


def simulate_portfolio(returns: pd.DataFrame, weights, horizon: int = 1,
                       n_sims: int = 10_000, seed: int = 0) -> np.ndarray:
    """Monte Carlo : mouvement brownien géométrique multi-actifs corrélé (Cholesky).
    Retourne les rendements simulés du portefeuille sur `horizon` jours."""
    rng = np.random.default_rng(seed)
    mu = returns.mean().values
    L = np.linalg.cholesky(returns.cov().values)
    z = rng.standard_normal((n_sims, horizon, len(mu)))
    daily = mu + z @ L.T                       # rendements log journaliers simulés
    asset_cum = daily.sum(axis=1)              # rendement log cumulé par actif
    return np.exp(asset_cum) @ np.asarray(weights) - 1


def var_monte_carlo(returns, weights, alpha=0.99, horizon=1, n_sims=10_000, seed=0) -> float:
    sims = simulate_portfolio(returns, weights, horizon, n_sims, seed)
    return -np.quantile(sims, 1 - alpha)


# ---------- Backtesting ----------
def backtest_var(r: pd.Series, alpha=0.99, window=250) -> pd.DataFrame:
    """VaR historique glissante (sur les `window` jours précédents) vs rendement réalisé."""
    var = r.rolling(window).apply(lambda x: -np.quantile(x, 1 - alpha), raw=True).shift(1)
    out = pd.DataFrame({"rendement": r, "VaR": var}).dropna()
    out["exception"] = out["rendement"] < -out["VaR"]
    return out


def kupiec_test(n_exceptions: int, n_obs: int, alpha=0.99) -> float:
    """Test de Kupiec (POF) : p-value ; H0 = taux d'exceptions conforme à 1-alpha."""
    p, x, n = 1 - alpha, n_exceptions, n_obs
    if x == 0:
        lr = -2 * n * np.log(1 - p)
    else:
        pi = x / n
        lr = -2 * ((n - x) * np.log(1 - p) + x * np.log(p)
                   - (n - x) * np.log(1 - pi) - x * np.log(pi))
    return 1 - stats.chi2.cdf(lr, df=1)


# ---------- Frontière efficiente ----------
def _perf(w, mu, cov):
    return w @ mu, np.sqrt(w @ cov @ w)


def efficient_frontier(returns: pd.DataFrame, n_points: int = 40, rf: float = 0.02):
    mu = returns.mean().values * TRADING_DAYS
    cov = returns.cov().values * TRADING_DAYS
    n = len(mu)
    bounds = [(0, 1)] * n                      # long-only
    cons_sum = {"type": "eq", "fun": lambda w: w.sum() - 1}
    w0 = np.ones(n) / n

    w_minvol = minimize(lambda w: _perf(w, mu, cov)[1], w0, bounds=bounds, constraints=[cons_sum]).x
    w_maxsharpe = minimize(lambda w: -(_perf(w, mu, cov)[0] - rf) / _perf(w, mu, cov)[1],
                           w0, bounds=bounds, constraints=[cons_sum]).x

    targets = np.linspace(_perf(w_minvol, mu, cov)[0], mu.max(), n_points)
    frontier = []
    for t in targets:
        cons = [cons_sum, {"type": "eq", "fun": lambda w, t=t: w @ mu - t}]
        res = minimize(lambda w: _perf(w, mu, cov)[1], w0, bounds=bounds, constraints=cons)
        if res.success:
            frontier.append((res.fun, t))
    return np.array(frontier), pd.Series(w_minvol, returns.columns), pd.Series(w_maxsharpe, returns.columns)
