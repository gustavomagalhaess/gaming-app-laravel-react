import React, { useState } from 'react'

interface Props {
    onConfirm: (name: string) => void
}

export default function PlayerNameModal({ onConfirm }: Props) {
    const [name, setName] = useState('')

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault()
        const trimmed = name.trim()
        if (trimmed) onConfirm(trimmed)
    }

    return (
        <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
            <div className="bg-white rounded-2xl p-8 w-96 shadow-2xl">
                <h2 className="text-2xl font-bold mb-2 text-gray-800">Welcome!</h2>
                <p className="text-gray-500 mb-6">Enter your name to start playing.</p>
                <form onSubmit={handleSubmit}>
                    <input
                        type="text"
                        value={name}
                        onChange={e => setName(e.target.value)}
                        placeholder="Your name"
                        maxLength={100}
                        autoFocus
                        className="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-lg focus:outline-none focus:border-green-500 mb-4"
                    />
                    <button
                        type="submit"
                        disabled={!name.trim()}
                        className="w-full bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3 rounded-lg font-semibold text-lg hover:bg-green-600 transition-colors"
                    >
                        Start Game
                    </button>
                </form>
            </div>
        </div>
    )
}
