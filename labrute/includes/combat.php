<?php
// Simulation d'un combat entre deux brutes (lignes de la table brute).
// Retourne l'identifiant du vainqueur et le compte rendu textuel.
function simuler_combat($brute1, $brute2) {
    $combattants = [$brute1, $brute2];
    $pv = [
        $brute1['id'] => (int)$brute1['pv'],
        $brute2['id'] => (int)$brute2['pv'],
    ];

    // Tirage au sort de la brute qui attaque en premier
    $a = random_int(0, 1);
    $lignes = [];
    $lignes[] = sprintf('%s (%d PV) affronte %s (%d PV).',
        $brute1['nom'], $pv[$brute1['id']], $brute2['nom'], $pv[$brute2['id']]);
    $lignes[] = $combattants[$a]['nom'] . ' attaque en premier.';

    $tour = 1;
    while (true) {
        $attaquant = $combattants[$a];
        $defenseur = $combattants[1 - $a];

        // Dégâts : force + aléa (0 à 2) - défense adverse, 1 minimum
        $degats = max(1, (int)$attaquant['force_brute'] + random_int(0, 2) - (int)$defenseur['defense']);
        $pv[$defenseur['id']] = max(0, $pv[$defenseur['id']] - $degats);

        $lignes[] = sprintf('Attaque %d : %s frappe %s et inflige %d dégâts. Il reste %d PV à %s.',
            $tour, $attaquant['nom'], $defenseur['nom'], $degats, $pv[$defenseur['id']], $defenseur['nom']);

        if ($pv[$defenseur['id']] === 0) {
            $vainqueur = $attaquant;
            break;
        }
        $a = 1 - $a;
        $tour++;
    }
    $lignes[] = $vainqueur['nom'] . ' remporte le combat !';

    return [
        'vainqueur_id' => (int)$vainqueur['id'],
        'compte_rendu' => implode("\n", $lignes),
    ];
}