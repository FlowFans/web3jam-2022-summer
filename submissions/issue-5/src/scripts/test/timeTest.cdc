pub fun main(): UInt64 {

    let attenuation: UFix64 = 3600.0 * 24.0 * 7.0;

    return UInt64(100.0 / attenuation);
}