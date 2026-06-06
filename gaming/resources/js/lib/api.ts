export interface CardData {
    id: number;
    suit: string;
    face: string;
    value: number;
}

export interface ComparisonResult {
    player_face: string;
    player_value: number;
    computer_face: string | null;
    computer_value: number | null;
    winner: 'player' | 'computer';
}

export interface GameResult {
    computer_cards: { face: string; value: number }[];
    comparisons: ComparisonResult[];
    player_score: number;
    computer_score: number;
    winner: 'player' | 'computer';
}

export interface PlayPayload {
    player_name: string;
    suit: string;
    selected_values: number[];
}

const JSON_HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
};

export async function fetchCards(suit: string): Promise<CardData[]> {
    const res = await fetch(`/api/cards/${encodeURIComponent(suit)}`, {
        headers: { 'Accept': 'application/json' },
    });
    if (!res.ok) throw new Error(`Failed to fetch cards: ${res.status}`);
    const json = await res.json();
    return json.data as CardData[];
}

export async function playGame(payload: PlayPayload): Promise<GameResult> {
    const res = await fetch('/api/game/play', {
        method: 'POST',
        headers: JSON_HEADERS,
        body: JSON.stringify(payload),
    });
    if (!res.ok) throw new Error(`Game play failed: ${res.status}`);
    return res.json() as Promise<GameResult>;
}
