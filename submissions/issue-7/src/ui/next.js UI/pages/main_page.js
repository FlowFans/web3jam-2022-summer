import Image from 'next/image'
import Link from 'next/link'
import {useState} from "react";
//import React from "react"
import testPic from '../public/test.jpg'
// import { TiChevronLeftOutline, TiChevronRightOutline } from 'https://cdn.skypack.dev/react-icons/ti';
import styles from '../ui/Home.module.css'

const CARDS = 10;
const MAX = 3;

export default function Home() {
    // Define a card
    const Card = ({ title, content, src, alt }) => {
        return (
            <div className={styles.card}>
                <h2>{title}</h2>
                <Image
                    src={src}
                    alt={alt}
                />
                <p>{content}</p>
                <Link href="/">
                    <a>购买</a>
                </Link>
            </div>
        )
    }



    const Carousel = ({children}) => {
        const [active, setActive] = useState(2);
        const count = React.Children.count(children);


        return (
            <div className={styles.carousel}>
                {active > 0 &&
                    <button
                        className={styles.nav.left}
                        onClick= {() => setActive(i => i-1)}
                    >"hh"</button>
                }
                React.Children.map(children, function(child, i) {
                    return ();
                })
                {children}.map((child, i) =>
                    <div className={styles.cardContainer}
                         style={
                             '--active': i === active ? 1 : 0,
                             '--offset': (active - i) / 3,
                             '--abs-offset': Math.abs(active - i) / 3,
                             'pointer-events': active === i ? 'auto' : 'none',
                             'opacity': Math.abs(active - i) >= MAX_VISIBILITY ? '0' : '1',
                             'display': Math.abs(active - i) > MAX_VISIBILITY ? 'none' : 'block'
                    }>child</div>
                );
                {active < count -1 &&
                    <button
                        className={styles.nav.right}
                        onClick= {() => setActive(i => i+1)}
                    >"hh"</button>
                }
            </div>
        )

    }

    return (
        <div>
            <Carousel children={[...new Array(CARDS)].map((_, i) =>
                <Card
                    title={'Card'+ (i+1)}
                    src={testPic}
                    alt={"hh"}
                    content={"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do "}
                ></Card>
            )}></Carousel>
        </div>
    )
}

