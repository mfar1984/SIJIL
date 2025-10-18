const PullToRefresh = ({ pullDistance, isRefreshing }) => {
  const rotation = Math.min((pullDistance / 80) * 360, 360)
  const opacity = Math.min(pullDistance / 80, 1)

  return (
    <div 
      className="pull-to-refresh-indicator"
      style={{
        transform: `translateY(${Math.min(pullDistance - 40, 40)}px)`,
        opacity: opacity
      }}
    >
      <span 
        className={`material-icons refresh-icon ${isRefreshing ? 'spinning' : ''}`}
        style={{
          transform: isRefreshing ? 'none' : `rotate(${rotation}deg)`
        }}
      >
        refresh
      </span>
    </div>
  )
}

export default PullToRefresh

