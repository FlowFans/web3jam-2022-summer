pub fun main(): [Int] {
  let vec = [1, 2, 3, 4, 6, 8];

  if let idx = vec.firstIndex(of: 6) {
     vec.remove(at: idx);
  }

  log(vec);

  return vec;
}
