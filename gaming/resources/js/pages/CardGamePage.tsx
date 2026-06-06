import React, { useState, useCallback, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import PlayerNameModal from '../components/PlayerNameModal'
import CardItem from '../components/CardItem'
import { SUIT_SYMBOL_MAP } from '../constants/cards'
import { fetchCards, playGame, type CardData, type GameResult } from '../lib/api'

type Phase = 'enter_name' | 'selecting' | 'results'

const SUITS = Object.keys(SUIT_SYMBOL_MAP)

function randomSuit(): string {
    return SUITS[Math.floor(Math.random() * SUITS.length)]
}

export default function CardGamePage() {
    const navigate = useNavigate()
    const [playerName, setPlayerName]   = useState('')
    const [phase, setPhase]             = useState<Phase>('enter_name')
    const [suit, setSuit]               = useState<string>(randomSuit)
    const [cards, setCards]             = useState<CardData[]>([])
    const [loadingCards, setLoadingCards] = useState(false)
    const [selected, setSelected]       = useState<Set<number>>(new Set())
    const [result, setResult]           = useState<GameResult | null>(null)
    const [playing, setPlaying]         = useState(false)

    const loadCards = useCallback(async (suitName: string) => {
        setLoadingCards(true)
        try {
            const data = await fetchCards(suitName)
            setCards(data)
        } catch {
            // Silently retry on next interaction — non-critical for MVP
        } finally {
            setLoadingCards(false)
        }
    }, [])

    useEffect(() => {
        if (phase === 'selecting') {
            loadCards(suit)
        }
    }, [phase, suit, loadCards])

    const handleNameConfirm = useCallback((name: string) => {
        setPlayerName(name)
        setPhase('selecting')
    }, [])

    const toggleCard = useCallback((value: number) => {
        setSelected(prev => {
            const next = new Set(prev)
            next.has(value) ? next.delete(value) : next.add(value)
            return next
        })
    }, [])

    const handlePlay = useCallback(async () => {
        if (selected.size === 0 || playing) return
        setPlaying(true)
        try {
            const gameResult = await playGame({
                player_name:     playerName,
                suit,
                selected_values: Array.from(selected),
            })
            setResult(gameResult)
            setPhase('results')
        } catch {
            // keep phase as selecting so player can retry
        } finally {
            setPlaying(false)
        }
    }, [selected, playerName, suit, playing])

    const handlePlayAgain = useCallback(() => {
        const newSuit = randomSuit()
        setSuit(newSuit)
        setSelected(new Set())
        setCards([])
        setResult(null)
        setPhase('selecting')
    }, [])

    const compMap = result
        ? Object.fromEntries(result.comparisons.map(c => [c.player_value, c]))
        : {}

    const winnerName = result?.winner === 'player' ? playerName : 'Computer'

    return (
        <div className="min-h-screen bg-green-800 flex flex-col items-center py-10 px-4">

            {phase === 'enter_name' && (
                <PlayerNameModal onConfirm={handleNameConfirm} />
            )}

            {/* Header */}
            <div className="text-center mb-8">
                <h1 className="text-4xl font-bold text-white mb-2">Card Game</h1>
                {playerName && (
                    <p className="text-green-200 text-sm">
                        Player: <span className="font-semibold text-white">{playerName}</span>
                    </p>
                )}
                <div className="mt-3 inline-flex items-center gap-2 bg-green-700 rounded-full px-5 py-2">
                    <span className="text-white font-semibold text-lg">{suit}</span>
                    <span className={`text-2xl ${suit === 'Hearts' || suit === 'Diamonds' ? 'text-red-400' : 'text-white'}`}>
                        {SUIT_SYMBOL_MAP[suit]}
                    </span>
                </div>
            </div>

            {/* Player's cards */}
            <div className="w-full max-w-4xl">
                <p className="text-green-300 text-xs font-semibold uppercase tracking-widest mb-4 text-center">
                    {phase === 'selecting' ? 'Select your cards' : 'Your selection'}
                </p>

                {loadingCards ? (
                    <div className="flex justify-center items-center py-12">
                        <div className="w-10 h-10 rounded-full border-4 border-green-400 border-t-transparent animate-spin" />
                    </div>
                ) : (
                    <div className="flex flex-wrap justify-center gap-2">
                        {cards.map(card => {
                            const isSelected = selected.has(card.value)
                            const comp       = compMap[card.value]
                            const showResult = phase === 'results' && isSelected
                            return (
                                <CardItem
                                    key={card.id}
                                    face={card.face}
                                    suitName={suit}
                                    selected={isSelected && phase === 'selecting'}
                                    onClick={phase === 'selecting' ? () => toggleCard(card.value) : undefined}
                                    dim={phase === 'selecting' && selected.size > 0 && !isSelected}
                                    result={showResult ? (comp?.winner === 'player' ? 'win' : 'lose') : null}
                                />
                            )
                        })}
                    </div>
                )}
            </div>

            {/* Play button */}
            {phase === 'selecting' && !loadingCards && (
                <div className="my-8">
                    <button
                        onClick={handlePlay}
                        disabled={selected.size === 0 || playing}
                        className="bg-yellow-400 disabled:bg-gray-500 disabled:text-gray-400 disabled:cursor-not-allowed text-gray-900 font-bold text-xl px-14 py-4 rounded-full shadow-lg hover:bg-yellow-300 active:scale-95 transition-all"
                    >
                        {playing
                            ? 'Playing…'
                            : selected.size === 0
                                ? 'Select at least one card'
                                : `Play — ${selected.size} card${selected.size > 1 ? 's' : ''}`}
                    </button>
                </div>
            )}

            {/* Results phase */}
            {phase === 'results' && result && (
                <div className="w-full max-w-4xl mt-6 flex flex-col gap-8">

                    {/* Head-to-head comparison */}
                    <div className="border-t-2 border-green-600 pt-8">
                        <p className="text-green-300 text-xs font-semibold uppercase tracking-widest mb-6 text-center">
                            Head-to-head
                        </p>
                        <div className="flex flex-wrap justify-center gap-6">
                            {result.comparisons.map((comp, i) => (
                                <div key={i} className="flex flex-col items-center gap-2">
                                    <CardItem
                                        face={comp.player_face}
                                        suitName={suit}
                                        result={comp.winner === 'player' ? 'win' : 'lose'}
                                    />
                                    <span className="text-green-400 text-xs font-medium">vs</span>
                                    {comp.computer_face !== null ? (
                                        <CardItem
                                            face={comp.computer_face}
                                            suitName={suit}
                                            result={comp.winner === 'computer' ? 'win' : 'lose'}
                                        />
                                    ) : (
                                        <div className="w-16 h-24 border-2 border-dashed border-green-600 rounded-lg flex items-center justify-center">
                                            <span className="text-green-500 text-xs text-center leading-tight px-1">No card</span>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Inline score banner */}
                    <div className="bg-green-700 rounded-xl p-6 text-center">
                        <p className="text-3xl font-bold text-white mb-1">{winnerName} wins!</p>
                        <p className="text-green-300 text-sm mb-4">
                            {result.winner === 'player' ? 'You beat the computer!' : 'Better luck next time!'}
                        </p>
                        <div className="flex justify-center gap-10 mb-6">
                            <div>
                                <div className="text-4xl font-bold text-yellow-300">{result.player_score}</div>
                                <div className="text-green-300 text-sm mt-1 truncate max-w-28">{playerName}</div>
                            </div>
                            <div className="self-center text-green-400 text-xl font-light">vs</div>
                            <div>
                                <div className="text-4xl font-bold text-red-400">{result.computer_score}</div>
                                <div className="text-green-300 text-sm mt-1">Computer</div>
                            </div>
                        </div>
                        <div className="flex justify-center gap-3">
                            <button
                                onClick={handlePlayAgain}
                                className="bg-yellow-400 text-gray-900 font-bold px-6 py-2 rounded-full hover:bg-yellow-300 active:scale-95 transition-all"
                            >
                                Play Again
                            </button>
                            <button
                                onClick={() => navigate('/leaderboard')}
                                className="border-2 border-green-500 text-green-200 font-semibold px-6 py-2 rounded-full hover:bg-green-600 transition-colors"
                            >
                                View Leaderboard
                            </button>
                        </div>
                    </div>

                </div>
            )}
        </div>
    )
}
