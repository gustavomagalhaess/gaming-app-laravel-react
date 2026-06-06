import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'

interface LeaderboardEntry {
    rank: number
    winner: string
    player_score: number
    computer_score: number
    played_at: string
}

type Status = 'loading' | 'success' | 'error'

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

export default function LeaderboardPage() {
    const [entries, setEntries] = useState<LeaderboardEntry[]>([])
    const [status, setStatus] = useState<Status>('loading')

    useEffect(() => {
        let cancelled = false
        fetch('/api/scores/top10', { headers: { Accept: 'application/json' } })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`)
                return res.json()
            })
            .then((body: { data: LeaderboardEntry[] }) => {
                if (!cancelled) {
                    setEntries(body.data)
                    setStatus('success')
                }
            })
            .catch(() => {
                if (!cancelled) setStatus('error')
            })
        return () => { cancelled = true }
    }, [])

    return (
        <div className="min-h-screen bg-gray-900 flex flex-col items-center py-12 px-4">
            <div className="w-full max-w-2xl">
                {/* Header */}
                <div className="text-center mb-10">
                    <h1 className="text-4xl font-bold text-white mb-2">Leaderboard</h1>
                    <p className="text-gray-400">Top 10 winners</p>
                </div>

                {/* Loading */}
                {status === 'loading' && (
                    <div className="flex justify-center py-20">
                        <div className="w-10 h-10 border-4 border-green-500 border-t-transparent rounded-full animate-spin" />
                    </div>
                )}

                {/* Error */}
                {status === 'error' && (
                    <div className="bg-red-900/40 border border-red-700 rounded-lg p-6 text-center">
                        <p className="text-red-300 font-medium">Failed to load leaderboard.</p>
                        <p className="text-red-400 text-sm mt-1">Please try again later.</p>
                    </div>
                )}

                {/* Table */}
                {status === 'success' && (
                    <>
                        {entries.length === 0 ? (
                            <div className="text-center py-20">
                                <p className="text-gray-400 text-lg">No games played yet.</p>
                                <p className="text-gray-500 text-sm mt-2">Be the first to set a record!</p>
                            </div>
                        ) : (
                            <div className="bg-gray-800 rounded-xl overflow-hidden shadow-xl">
                                <table className="w-full">
                                    <thead>
                                        <tr className="bg-gray-700 text-gray-300 text-sm uppercase tracking-wider">
                                            <th className="px-4 py-3 text-center w-12">#</th>
                                            <th className="px-4 py-3 text-left">Winner</th>
                                            <th className="px-4 py-3 text-center">Player</th>
                                            <th className="px-4 py-3 text-center">Computer</th>
                                            <th className="px-4 py-3 text-right">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {entries.map((entry, i) => (
                                            <tr
                                                key={i}
                                                className="border-t border-gray-700 hover:bg-gray-750 transition-colors"
                                            >
                                                <td className="px-4 py-4 text-center">
                                                    {entry.rank === 1 && <span className="text-yellow-400 text-lg">🥇</span>}
                                                    {entry.rank === 2 && <span className="text-gray-300 text-lg">🥈</span>}
                                                    {entry.rank === 3 && <span className="text-amber-600 text-lg">🥉</span>}
                                                    {entry.rank > 3 && (
                                                        <span className="text-gray-500 font-medium">{entry.rank}</span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-4">
                                                    <span className={`font-semibold ${entry.winner === 'Computer' ? 'text-red-400' : 'text-green-400'}`}>
                                                        {entry.winner}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-4 text-center text-white font-bold">
                                                    {entry.player_score}
                                                </td>
                                                <td className="px-4 py-4 text-center text-gray-300">
                                                    {entry.computer_score}
                                                </td>
                                                <td className="px-4 py-4 text-right text-gray-400 text-sm">
                                                    {formatDate(entry.played_at)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </>
                )}

                {/* Back to Games */}
                <div className="mt-8 text-center">
                    <Link
                        to="/"
                        className="inline-block bg-green-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-600 transition-colors"
                    >
                        Back to Games
                    </Link>
                </div>
            </div>
        </div>
    )
}
