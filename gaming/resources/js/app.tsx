import React from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import HomePage from './pages/HomePage'
import CardGamePage from './pages/CardGamePage'
import LeaderboardPage from './pages/LeaderboardPage'
import '../css/app.css'

function App() {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<HomePage />} />
                <Route path="/card-game" element={<CardGamePage />} />
                <Route path="/leaderboard" element={<LeaderboardPage />} />
            </Routes>
        </BrowserRouter>
    )
}

const root = document.getElementById('app')
if (!root) throw new Error('Root element #app not found')
createRoot(root).render(<App />)
