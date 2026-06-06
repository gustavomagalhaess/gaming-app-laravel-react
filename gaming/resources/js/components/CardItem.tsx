import React from 'react'
import { SUIT_SYMBOL_MAP } from '../constants/cards'

interface Props {
    face: string
    suitName: string
    selected?: boolean
    onClick?: () => void
    dim?: boolean
    result?: 'win' | 'lose' | null
}

export default function CardItem({ face, suitName, selected = false, onClick, dim = false, result = null }: Props) {
    const symbol = SUIT_SYMBOL_MAP[suitName] ?? suitName
    const isRed  = suitName === 'Hearts' || suitName === 'Diamonds'

    let borderClass = 'border-gray-200 hover:border-gray-400'
    if (selected) borderClass = 'border-yellow-400 ring-2 ring-yellow-300 shadow-yellow-200'
    if (result === 'win')  borderClass = 'border-yellow-400 ring-2 ring-yellow-300'
    if (result === 'lose') borderClass = 'border-gray-300'

    return (
        <div
            onClick={onClick}
            role={onClick ? 'button' : undefined}
            className={[
                'relative w-16 h-24 bg-white rounded-lg border-2 shadow-md',
                'flex flex-col items-center justify-center select-none',
                'transition-all duration-150',
                borderClass,
                dim ? 'opacity-40' : '',
                onClick ? 'cursor-pointer hover:shadow-lg hover:-translate-y-1' : 'cursor-default',
            ].join(' ')}
        >
            <span className={`text-lg font-bold leading-none ${isRed ? 'text-red-600' : 'text-gray-900'}`}>
                {face}
            </span>
            <span className={`text-xl leading-none ${isRed ? 'text-red-600' : 'text-gray-900'}`}>
                {symbol}
            </span>
            {result === 'win' && (
                <span className="absolute -top-2 -right-2 bg-yellow-400 text-xs font-bold text-gray-900 rounded-full w-5 h-5 flex items-center justify-center shadow">
                    +1
                </span>
            )}
        </div>
    )
}
