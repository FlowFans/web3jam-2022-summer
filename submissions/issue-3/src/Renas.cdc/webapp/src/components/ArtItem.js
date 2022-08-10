import React from 'react'
import styled from 'styled-components'

const Wrapper = styled.div`
    flex: 0 0 90%;
    width: 90%;
    max-height: 100%;
    min-height: 200px;
    overflow: hidden;
    background: rgb(255, 255, 255);
    box-shadow: rgb(0 0 0 / 2%) 0px 10px 20px 15px;
    border-radius: 10px;
    margin-right: 20px;

    &:last-child {
        margin-right: 0;
    }
`

const Image = styled.img`
    max-width: 100%;
    height: auto;
    margin-bottom: -10px;
`

const VideoWrapper = styled.video`
    max-width: 100%;
    height: auto;
    margin-bottom: -10px;
    border-top-right-radius: 10px;
    border-top-left-radius: 10px;
`

const Content = styled.div`
    padding: 16px;
`;

const Title = styled.div`
    font-weight: 600;
    font-size: 13px;
`

const Description = styled.div`
    font-weight: 400;
    font-size: 12px;
`

const Video = ({ src }) => (
    <VideoWrapper controls>
        <source src={src} type="video/mp4" />
    </VideoWrapper>
)

const Item = ({ name, description, mediaType, mediaHash, isArt }) => {
    const Media = mediaType.includes('video') ? Video : Image

    return (
        <Wrapper>
            <Media
                isArt={isArt}
                src={`https://${mediaHash}.ipfs.dweb.link`}
            />
            {!isArt && <Content>
                <Title>{name}</Title>
                <Description>{description}</Description>
            </Content>}
        </Wrapper>
    )
}

export default Item
