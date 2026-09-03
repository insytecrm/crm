import * as React from "react"
import {
  MotionIcon,
  type AnimationType,
  type EntranceAnimationType,
  type MotionIconProps,
  type TriggerType,
} from "motion-icons-react"

import { cn } from "@/lib/utils"

export type { AnimationType, EntranceAnimationType, MotionIconProps, TriggerType }
export { MotionIcon }

export type AnimatedIconProps = MotionIconProps

function AnimatedIcon({
  className,
  size = 16,
  trigger = "hover",
  ...props
}: AnimatedIconProps) {
  return (
    <MotionIcon
      className={cn("inline-flex shrink-0", className)}
      size={size}
      trigger={trigger}
      {...props}
    />
  )
}

export { AnimatedIcon }
