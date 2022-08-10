import styles from './Backpack.less';

export default function Backpack() {
    const [user, setUser] = useState({loggedIn: null})
    const { Header, Footer, Sider, Content } = Layout;
 
  
    return (
      <div className={styles.bg}>
        <div>Profile Name: {name ?? "--"}</div> {/* NEW */}
        <button onClick={fclquery}>Send Query</button>
        <button onClick={fclmutate}>fclmutate</button>
        {user.loggedIn
          ? <AuthedState />
          : <UnauthenticatedState />
        }
      </div>
    );
  }
  