import type { ComponentProps, ReactNode } from 'react'

import { PageCard } from './PageCard'

export type CardProps = ComponentProps<typeof PageCard> & {
  children: ReactNode
}

export function Card(props: CardProps) {
  return <PageCard {...props} />
}
