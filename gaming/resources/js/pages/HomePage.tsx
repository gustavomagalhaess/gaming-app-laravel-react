import React from 'react'
import { Link } from 'react-router-dom'

export default function HomePage() {
    return (
        <div className="min-h-screen bg-gray-100 flex items-center justify-center">
            <div className="text-center">
                <h1 className="text-4xl font-bold mb-8">Gaming App</h1>
                <Link
                    to="/card-game"
                    className="inline-block bg-green-700 text-white px-8 py-4 rounded-lg text-xl font-semibold hover:bg-green-600 transition-colors"
                >
                    Card Game
                </Link>
            </div>
        </div>
    )
}
