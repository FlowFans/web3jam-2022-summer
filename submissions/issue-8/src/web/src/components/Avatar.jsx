import PropTypes from "prop-types"
import publicConfig from "src/global/publicConfig"

const avatarUrl = hash =>
  encodeURI(
    `${publicConfig.avatarUrl.trim().replace(/\/$/, "")}/avatar/${hash}.svg`
  )

export default function Avatar({address}) {
  if (address === publicConfig.flowAddress) {
    return (
      <img src="/images/badge-logo.svg" alt="Badge" width="100%" />
    )
  }

  return (
    // eslint-disable-next-line @next/next/no-img-element
    <div className="border border-gray-200 rounded-full w-full h-full">
      {typeof address !== "undefined" && (
        <img
          src={avatarUrl(`${address}-${publicConfig.appTitle}`)}
          alt={address}
          className="rounded-full"
        />
      )}
    </div>
  )
}

Avatar.propTypes = {
  address: PropTypes.string,
}
